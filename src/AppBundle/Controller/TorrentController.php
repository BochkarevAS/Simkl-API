<?php

namespace AppBundle\Controller;

use AppBundle\Service\TorrentService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TorrentController extends Controller {

    private $torrentService;

    public function __construct(TorrentService $torrentService) {
        $this->torrentService = $torrentService;
    }

    /**
     * @Route("/torrent/", name="torrent_start")
     */
    public function getTorrent(Request $request) {
        $token = $request->query->get('token');
        $movies = $this->torrentService->getMovies($token);

        return $this->render('user/movies.html.twig', [
            'movies' => $movies
        ]);
    }

    /**
     * @Route("/torrent/movie/", name="torrent_movie")
     */
    public function getMovie(Request $request) {
        $name = $request->query->get('name');
        $link = $this->torrentService->getContents($name);
        $url = 'https://nnmclub.to/forum/' . $link;

        return $this->redirect($url);
    }
}