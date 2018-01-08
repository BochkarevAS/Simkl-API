<?php

namespace AppBundle\Service;

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client;

class SimklParser {

    const CLIENT_ID = '9e36b457bc701379ef59849daf7ecf71e02549f551442b962b52a34b105c5d82';

    private $cache;

    public function __construct(Cache $cache) {
        $this->cache = $cache;
    }

    public function authorize() {

        $link = [
            'client_id'     => '9e36b457bc701379ef59849daf7ecf71e02549f551442b962b52a34b105c5d82',
            'redirect_uri'  => 'http://127.0.0.1:8000/simkl/token',
            'response_type' => 'code'
        ];

        $url = "https://simkl.com/oauth/authorize?" . http_build_query($link);

        header('Location: ' . $url);
        die;
    }

    public function token($code) {

        $token = null;

        $json = [
            'code' => $code,
            'client_id' => self::CLIENT_ID,
            'client_secret' => '88fcbdd1433112ac07872b573ad5fed427675ad0d1ee4dde5f8b6580800bc512',
            'redirect_uri' => 'http://127.0.0.1:8000/simkl/token',
            'grant_type' => 'authorization_code'
        ];

        $json = json_encode($json);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://api.simkl.com/oauth/token");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $list = json_decode($response, true);

        foreach ($list as $key => $value) {
            if ($key === 'access_token') {
                $token = $value;
            }
        }

        return $token;
    }

    public function activities($token) {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://api.simkl.com/sync/all-items/movies/?extended=full");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "authorization: Bearer $token",
            "simkl-api-key: " . self::CLIENT_ID
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        var_dump($response);
        die;

        return json_decode($response, true);
    }
}