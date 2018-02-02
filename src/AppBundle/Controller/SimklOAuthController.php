<?php

namespace AppBundle\Controller;

use AppBundle\Service\SimklService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SimklOAuthController extends Controller  {

    private $simklService;

    public function __construct(SimklService $simklService) {
        $this->simklService = $simklService;
    }

    /**
     * @Route("/oauth", name="authorize_start")
     */
    public function redirectToAuthorizationAction() {
        $token = $this->get('session')->get('token');

        if ($token) {
            return $this->render('main/homepage.html.twig', [
                'token' => $token
            ]);
        }

        $url = $this->simklService->redirectToAuthorization();
        return $this->redirect($url);
    }

    /**
     * @Route("/oauth/receive/", name="oauth_receive")
     */
    public function receiveAuthorizationCodeAction(Request $request) {
        $code = $request->query->get('code');
        $token = $this->simklService->receiveAuthorizationCode($code);
        $this->get('session')->set('token', $token);

        return $this->render('main/homepage.html.twig', [
            'token' => $token
        ]);
    }
}