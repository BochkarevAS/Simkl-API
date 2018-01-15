<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CoopController extends Controller {

    /**
     * @Route("/oauth1", name="authorize_start1")
     */
    public function redirectToAuthorizationAction() {
        $state = md5(uniqid(mt_rand(), true));

        $http = [
            'response_type' => 'code',
            'client_id' => 'Ant-vl',
            'redirect_uri' => 'http://127.0.0.1:8000/oauth/receive',
            'scope' => 'eggs-count profile',
            'state' => $state
        ];

        $url = 'http://coop.apps.knpuniversity.com/authorize?' . http_build_query($http);
        return $this->redirect($url);
    }

    /**
     * @Route("/oauth1/receive/", name="oauth1_receive")
     */
    public function receiveAuthorizationCodeAction(Request $request) {
        $code = $request->query->get('code');

        if (!$code) {
            $error = $request->get('error');
            $errorDescription = $request->get('error_description');

            return $this->render('error/failed_authorization.html.twig', [
                'error' => $error,
                'errorDescription' => $errorDescription
            ]);
        }

        $client = new Client([
            'base_uri' => 'http://coop.apps.knpuniversity.com',
            'timeout' => 10
        ]);

        $response = $client->request('POST', '/token', [
            'form_params' => [
                'client_id'     => 'Ant-vl',
                'client_secret' => 'd3366efdcdbee49ecd5748dc5e8bc1e4',
                'code'          => $code,
                'grant_type'    => 'client_credentials'
            ]
        ]);

        $body = $response->getBody();
        $contents = $body->getContents();

        $list = json_decode($contents, true);
        $accessToken = $list['access_token'];
        $expiresIn = $list['expires_in'];
        $expiresAt = new \DateTime('+' . $expiresIn . ' seconds');

        if (!isset($accessToken)) {
            return $this->render('error/failed_token_request.twig', [
                'response' => $list ? $list : $response
            ]);
        }

        $response = $client->request('GET', '/api/me', [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken]
        ]);

        $body = $response->getBody();
        $contents = $body->getContents();
        $list = json_decode($contents, true);

        // Запишем данные в БД
        $em = $this->getDoctrine()->getManager();
        $arr = $em->getRepository('AppBundle:User')->findTokenByStatusId($list['id']);

        if (!$arr) {
            $user = new User();
        } else {
            $user = $arr[0];
        }

        $user->setFirstName($list['firstName']);
        $user->setLastName($list['lastName']);
        $user->setEmail($list['email']);
        $user->setToken($accessToken);
        $user->setStatus($list['id']);
        $user->setExpiresAt($expiresAt->format('Y-m-d'));

        $em->persist($user);
        $em->flush();

        return $this->render('main/homepage.html.twig', [
            'user' => $user
        ]);
    }
}