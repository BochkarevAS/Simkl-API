<?php

namespace AppBundle\Service;

use Doctrine\Common\Cache\Cache;

class SimklParser {

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

        $clientId = '9e36b457bc701379ef59849daf7ecf71e02549f551442b962b52a34b105c5d82';
        $clientSecret = '88fcbdd1433112ac07872b573ad5fed427675ad0d1ee4dde5f8b6580800bc512';
        $redirectUri = 'http://127.0.0.1:8000/simkl/token';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.simkl.com/oauth/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
          \"code\": \"$code\",
          \"client_id\": \"$clientId\",
          \"client_secret\": \"$clientSecret\",
          \"redirect_uri\": \"$redirectUri\",
          \"grant_type\": \"authorization_code\"
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);

        die;
    }

}
