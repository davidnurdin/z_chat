<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ChatController extends AbstractController
{

    #[Route('/chat', name: 'app_chat')]
    public function app_chat(HubInterface $hub,Request $request): Response
    {
        $nick = $_GET['nick'] ?? 'Anonyme' ;

        $jwtPrivate = $hub->getFactory()->create(
            [
                // subscribe
                $request->getSchemeAndHttpHost() . '/private/666/{userId}'
            ],
            [
                // publish
                $request->getSchemeAndHttpHost() . '/private/666/{userId}'
            ],
            [
                'mercure' => [
                    'payload' => [
                        'username' => $nick,
                        'ip' => 'xxx.xxx.xxx.xxx'
                    ]]
            ]
        ) ;

        return $this->render('home/chat.html.twig', [
            'nick' => $nick,
            'sseUrlPrivateJwt' => $jwtPrivate,
            'controller_name' => 'HomeController',
        ]);
    }

}
