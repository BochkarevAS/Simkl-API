<?php

namespace AppBundle\Controller;

use AppBundle\Service\SimklParser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/simkl")
 */
class SimklController extends Controller {

    private $simklParser;

    public function __construct(SimklParser $simklParser) {
        $this->simklParser = $simklParser;
    }

    /**
     * @Route("/authorize", name="authorize_simkl")
     */
    public function authorizeAction() {

        $this->simklParser->authorize();

        return new Response();
    }

    /**
     * @Route("/token", name="token_simkl")
     */
    public function tokenAction(Request $request) {

        $code = $request->query->get('code');
        $token = $this->simklParser->token($code);

        if (!$token) {
            return new Response('<h1>Token not found !!!</h1>');
        }

        return $this->redirectToRoute('activities_simkl', ['token' => $token]);
    }

    /**
     * @Route("/activities/{token}", name="activities_simkl")
     */
    public function activitiesAction($token) {

        $list = $this->simklParser->activities($token);

        return $this->render('simkl/index.html.twig', [
           'list' => $list
        ]);
    }
}
