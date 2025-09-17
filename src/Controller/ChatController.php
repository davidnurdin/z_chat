<?php

namespace App\Controller;
use App\Service\MercureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

final class ChatController extends AbstractController
{

    public function __construct(private MercureService $mercureService,private HubInterface $hub)
    {

    }


    #[Route('/chat/connect/{nick}', name: 'chat_connect')]
    public function chat_connect(Request $request,string $nick): JsonResponse
    {
        $result = ['action' => 'connected' , 'nick' => $nick] ;
        $update = new Update(
            $request->getSchemeAndHttpHost() . '/public',
            json_encode($result)
        );

        $this->hub->publish($update);

        return new JsonResponse("ok");
    }

    #[Route('/chat/users', name: 'chat_users')]
    public function chat_users(): JsonResponse
    {
        $users = $this->mercureService->FetchActiveSubscribers('/public');
        return new JsonResponse($users);
    }

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
