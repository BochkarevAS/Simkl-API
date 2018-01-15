<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

// cd C:/PHP_projects/Simkl_parser/bin/

class MainController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction() {

        return $this->render('main/homepage.html.twig', []);
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction() {

        return $this->render('user/login.html.twig', []);
    }
}
