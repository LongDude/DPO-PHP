<?php

namespace App\Controller;

use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function index(): Response
    {   
        $this->getUser();

        return $this->render('main/index.html.twig', [
            'user_roles' => $this->getUser()?->getRoles(),
            'user_id' => $this->getUser()?->getId(),
            'user_email' => $this->getUser()?->getUserIdentifier(),
        ]);
    }

    #[Route('/book/new', name:'api_book_add', methods: ["POST"])]
    public function add_book(): JsonResponse {
        
    }
    
    #[Route('/book/{id}', name:'api_book_edit', methods: ["POST"])]
    public function edit_Book(): JsonResponse {

    }

    #[Route('/book/table', name: 'api_book_table', methods: ["POST"])]
    public function get_book_table(): JsonResponse {

    }
}