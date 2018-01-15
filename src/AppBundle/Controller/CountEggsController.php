<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CountEggsController extends Controller {

    /**
     * @Route("/count/eggs", name="count_eggs")
     */
    public function countEggsAction(Request $request) {
        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository('AppBundle:User')->findTokenByStatusId($id);

        if (!$list) {
            throw new \Exception('User not found');
        } else {
            $user = $list[0];
        }

        if (!$user->getToken() || !$user->getStatus()) {
            throw new \Exception('Somehow you got here, but without a valid COOP access token! Re-authorize!');
        }

        if (!$user->getExpiresAt()) {
            return $this->redirectToRoute('authorize_start');
        }

        $client = new Client([
            'base_uri' => 'http://coop.apps.knpuniversity.com',
            'timeout' => 10
        ]);

        $response = $client->request('POST', '/api/' . $user->getStatus() . '/eggs-count', [
            'headers' => ['Authorization' => 'Bearer ' . $user->getToken()]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception($response->getBody());
        }

        return $this->render('main/homepage.html.twig', [
            'user' => $user
        ]);
    }
}