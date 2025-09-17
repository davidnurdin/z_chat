<?php

namespace App\Controller;

use App\Form\NickFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {

        // add the form
        $form = $this->createForm(NickFormType::class);

        // if the form is submitted
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // redirect to chat route with the nickname as parameter
            return $this->redirectToRoute('app_chat', ['nick' => $data['nickname']]);
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form,
        ]);
    }
}
