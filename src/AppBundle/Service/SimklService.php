<?php

namespace AppBundle\Service;

use AppBundle\Entity\Movie;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;

class SimklService {

    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function redirectToAuthorization() {
        $state = md5(uniqid(mt_rand(), true));

        $http = [
            'client_id'     => '9e36b457bc701379ef59849daf7ecf71e02549f551442b962b52a34b105c5d82',
            'redirect_uri'  => 'http://127.0.0.1:8000/oauth/receive',
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
                'client_id'     => '9e36b457bc701379ef59849daf7ecf71e02549f551442b962b52a34b105c5d82',
                'client_secret' => '88fcbdd1433112ac07872b573ad5fed427675ad0d1ee4dde5f8b6580800bc512',
                'redirect_uri'  => 'http://127.0.0.1:8000/oauth/receive',
                'grant_type'    => 'authorization_code'
            ]
        ]);

        $body = $response->getBody();
        $contents = json_decode($body->getContents(), true);
        $token = $contents['access_token'];

        if (!isset($token)) {
            throw new \Exception('Token not found');
        }

        $response = $client->request('POST', '/sync/all-items/movies/?extended=full', [
            'headers' => [
                'authorization' => 'Bearer ' . $token,
                'simkl-api-key' => '9e36b457bc701379ef59849daf7ecf71e02549f551442b962b52a34b105c5d82'
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

        return $movie;
    }
}