<?php

namespace App\Controller;

use App\Enum\BooksFilter;
use App\Repository\BookRepository;
use DateTimeImmutable;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted("ROLE_USER")]
    public function add_book(Request $request, BookRepository $bookRepository): JsonResponse {
        $fileErrors = $bookRepository->addBook(
            $request->request->get('name'),
            $request->request->get('author') ?? "Аноним",
            $request->files->get('cover') ?? null,
            $request->files->get('file') ?? null,
            $this->getUser(),
            new DateTimeImmutable($request->request->get('read_date')),
            $request->request->get('allow_download'),
        );

        if (count($fileErrors) > 0){
            return $this->json([
                'status' => 'error',
                'message' => 'Book added with errors',
                'errors' => $fileErrors,
            ]);
        }
        return $this->json([
            'status' => 'success',
            'message' => 'Book added successfully',
            'redirect' => $this->generateUrl('app_root'),
        ]);
    }
    
    #[Route('/book/{id}', name:'api_book_get', methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function get_book(BookRepository $bookRepository, int $id): JsonResponse {
        $book = $bookRepository->find($id);

        if (!$book) return new JsonResponse(null, 400);
        return $this->json([
            "Book ID" => $book->getId(),
            "Book Name" => $book->getName(),
            "Book Author" => $book->getAuthor(),
            "Has Cover" => !empty($book->getCoverPath()),
            "Has File" => !empty($book->getFilePath()),
            "Book Upload date" => $book->getUploadDate()?->format('d-m-Y'),
            "Book Read date" =>$book->getReadDate()?->format('d-m-Y'),
            "Allow download" => $book->getAllowDownload(),
        ]);
    }

    #[Route('/book/{id}/cover', name:'api_book_get_cover', methods: ["GET"])]
    public function get_book_cover(BookRepository $bookRepository, int $id): BinaryFileResponse {
        $book = $bookRepository->find($id);
        
        if ($book == null){
            return new Response("Книга не найдена", 400);
        }
        if ($book instanceof Book){};
        if ($book->getCoverPath() == null){
            return new Response("У книги нет обложки", 400);
        }
        return new BinaryFileResponse($bookRepository->loadCover($book));
    }

    #[Route('/book/{id}/file', name:'api_book_get_file', methods: ["GET"])]
    public function get_book_file(BookRepository $bookRepository, int $id): BinaryFileResponse|Response {
        $book = $bookRepository->find($id);
        
        if ($book == null){
            return new Response("Книга не найдена", 400);
        }
        if ($book instanceof Book){};
        if ($book->getCoverPath() == null){
            return new Response("У книги нет загруженного файла", 400);
        }

        if ($this->isGranted("ROLE_ADMIN") || $book->getUploader() === $this->getUser() || $book->getAllowDownload()){
            return new BinaryFileResponse($bookRepository->loadFile($book));
        }
        else {
            return new Response("Недостаточно прав (запрет на публичное скачивание)", 403);
        }
    }

    #[Route('/book/{id}/edit', name:'api_book_edit', methods: ["POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit_Book(int $id, Request $request, BookRepository $bookRepository): Response {
        $book = $bookRepository->find($id);
        
        $bookRepository->editBook(
            $book, 
            $request->request->get('name'), 
            $request->request->get('author'),
            $request->files->get('cover'),
            $request->files->get('file'),
            new DateTimeImmutable($request->request->get('read_date')),
            $request->request->get('allow_download'),
        );
        
        return new Response(200);
    }

    #[Route('/list', name: 'api_book_table', methods: ["GET"])]
    public function get_book_table(Request $request, BookRepository $bookRepository): JsonResponse {
        $limit = $request->query->get('limit', 10);
        $offset = $request->query->get('offset', 0);
        $filter = $request->query->get('userFilter', 'all');
        $orderField = $request->query->get('orderField', 'name');
        $orderBy = $request->query->get('orderBy', 'asc');
        $qname = $request->query->get('qname', null);
        $qauthor = $request->query->get('qauthor', null);
        
        $filter = match($filter){
            'user' => BooksFilter::USER,
            'others' => BooksFilter::NONUSER,
            default => BooksFilter::ALL
        };

        $repositoryRequest = $bookRepository->listBooks(
            $limit, $offset, $filter, $orderField, $orderBy, $qname, $qauthor, $this->getUser()
        );
        $total = $repositoryRequest['totalElements'];
        $content = $repositoryRequest['content'];

        return $this->json([
            'content' => array_map(function($row){
                return [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'author' => $row['author'],
                    'linkCover' => $row['hasCover'],
                    'linkFile' => ($this->isGranted('ROLE_ADMIN') || $this->getUser() == $row['uploader'] || $row['allowPublicDownload']) && $row['hasFile'],
                    'hasFile' => $row['hasFile'],
                    'canEdit' => ($this->isGranted('ROLE_ADMIN') || $this->getUser() == $row['uploader']),
                    'allowPublicDownload' => $row['allowPublicDownload'],
                    'uploadDate' => $row['uploadDate'],
                    'readDate' =>$row['readDate'],
                ];
            }, $content),
            'total' => $total
        ]);
    }

    #[Route('/book/{id}', name:'api_book_delete', methods: ["DELETE"])]
    #[IsGranted("ROLE_USER")]
    public function delete_book(BookRepository $bookRepository, int $id): Response {
        $book = $bookRepository->find($id);
        if ($book == null){
            return new Response('Книги с таким id не существует', 400);
        }
        $bookRepository->removeBook($book);
        return new Response("Книга успешно удалена", 200);
    }
}