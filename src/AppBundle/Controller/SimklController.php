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

    /**
     * @Route("/authorize", name="authorize_simkl")
     */
    public function authorizeAction(SimklParser $simklParser) {

        $simklParser->authorize();

        return new Response();
    }

    /**
     * @Route("/token", name="token_simkl")
     */
    public function tokenAction(Request $request, SimklParser $simklParser) {

        $code = $request->query->get('code');

        $simklParser->token($code);

        return $this->render('simkl/index.hnml.twig', []);
    }
}
