<?php

namespace AppBundle\Service;

use AppBundle\Entity\Movie;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;

class SimklService {

    const CLIENT_ID = '9e36b457bc701379ef59849daf7ecf71e02549f551442b962b52a34b105c5d82';
    const REDIRECT_URI = 'http://127.0.0.1:8000/oauth/receive';
    const CLIENT_SECRET = '88fcbdd1433112ac07872b573ad5fed427675ad0d1ee4dde5f8b6580800bc512';

    private $manager;
    private $cache;

    public function __construct(ObjectManager $manager, Cache $cache) {
        $this->manager = $manager;
        $this->cache = $cache;
    }

    public function redirectToAuthorization() {
        $state = md5(uniqid(mt_rand(), true));

        $http = [
            'client_id'     => self::CLIENT_ID,
            'redirect_uri'  => self::REDIRECT_URI,
            'response_type' => 'code',
            'state'         => $state
        ];

        $url = "https://simkl.com/oauth/authorize?" . http_build_query($http);
        return $url;
    }

    public function receiveAuthorizationCode($code) {

        if (!$code) {
            throw new \Exception('Code not found');
        }

        $client = new Client([
            'base_uri' => 'https://api.simkl.com',
            'timeout' => 10
        ]);

        $response = $client->request('POST', '/oauth/token', [
            'json' => [
                'code'          => $code,
                'client_id'     => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET,
                'redirect_uri'  => self::REDIRECT_URI,
                'grant_type'    => 'authorization_code'
            ]
        ]);

        $body = $response->getBody();
        $contents = json_decode($body->getContents(), true);
        $token = $contents['access_token'];

        if (!isset($token)) {
            throw new \Exception('Token not found');
        }

//        if ($this->cache->contains($code)) {
//            return $this->cache->fetch($code);
//        }
//        $this->cache->save($code, $token);

        $response = $client->request('POST', '/sync/all-items/movies/?extended=full', [
            'headers' => [
                'authorization' => 'Bearer ' . $token,
                'simkl-api-key' => self::CLIENT_ID
            ]
        ]);

        $body = $response->getBody();
        $contents = json_decode($body->getContents(), true);
        $movies = $this->manager->getRepository('AppBundle:Movie')->findByTokenMovie($token);

        foreach ($movies as $movie) {
            $this->manager->remove($movie);
            $this->manager->flush();
        }

        foreach ($contents['movies'] as $item) {
            $movie = new Movie();

            $movie->setName($item['movie']['title']);
            $movie->setYear($item['movie']['year']);
            $movie->setToken($token);
            $movie->setStatus($item['status']);

            $this->manager->persist($movie);
            $this->manager->flush();
        }

        return $token;
    }
}