<?php

namespace App\Controller;

use App\Repository\BookRepository;
use DateTimeImmutable;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;

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
    
    #[Route('/book/{id}', name:'api_book_get', methods: ["GET"])]
    public function get_book(EntityManagerInterface $entityManager, int $id): JsonResponse {
        $book = $entityManager->getRepository(Book::class)->find($id);
        return $this->json($book);
    }

    #[Route('/book/{id}', name:'api_book_edit', methods: ["PUT"])]
    public function edit_Book(EntityManagerInterface $entityManager, int $id, Request $request, BookRepository $bookRepository): JsonResponse {
        $book = $entityManager->getRepository(Book::class)->find($id);

        $name = $request->request->get('name');
        $author = $request->request->get('author');
        $bookRepository->editBook(
            $book, 
            $name, 
            $author,
            $request->files->get('cover'),
            $request->files->get('file'),
            new DateTimeImmutable($request->request->get('read_date')),
            $request->request->get('allow_download'),
        );
        
        return $this->json($book);
    }

    #[Route('/book/table', name: 'api_book_table', methods: ["GET"])]
    public function get_book_table(Request $request, BookRepository $bookRepository): JsonResponse {
        $limit = $request->query->get('limit', 10);
        $offset = $request->query->get('offset', 0);
        $books = $bookRepository->listBooks($limit, $offset);
        return $this->json($books);
    }
}