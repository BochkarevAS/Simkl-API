<?php

namespace AppBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class TorrentService {

    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function getContents($movie) {

//        $client = new Client([
//            'base_uri' => 'https://nnmclub.to/forum/tracker.php'
//        ]);
//
//        $response = $client->request('GET');
//        $html = (string) $response->getBody();
//
//        $file = fopen('C:\PHP_projects\Simkl_parser\file.txt', 'w');
//        fwrite($file, $html);
//        fclose($file);

        $html = file_get_contents('C:\PHP_projects\Simkl_parser\file.txt'); // Для теста...

        $crawler = new Crawler($html);
        $nodes = $crawler->filter('a[class^="genmed topic"]')->each(function (Crawler $node) {
            return [
                'link' => $node->attr('href'),
                'name' => $node->text()
            ];
        });

        $movie = preg_quote($movie, '/');

        foreach ($nodes as $node) {

            if (preg_match("~$movie~", $node['name'], $matches)) {
                $link = $node['link'];
            }
        }

        return $link;
    }

    public function getMovies($token) {
        $movies = $this->manager->getRepository('AppBundle:Movie')->findByTokenMovie($token);
        return $movies;
    }
}