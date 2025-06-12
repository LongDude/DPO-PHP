<?php

namespace App\Repository;
use App\Service\FileUploader;
use App\Enum\BooksFilter;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Dotenv\Exception\PathException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class BookRepository extends ServiceEntityRepository {




    public function __construct(
        ManagerRegistry $registry,
        private FileUploader $uploader,
    ) {
        parent::__construct($registry, Book::class);
    }

    /**
     * Возвращает список книг с учетом фильтров и пагинации
     * @return void
     */
    public function listBooks(
        int $limit = 12, 
        int $offset = 0,
        BooksFilter $filter = BooksFilter::ALL,
        string $orderField = 'name',
        string $orderDir = 'asc',
        ?string $qname = null,
        ?string $qauthor = null,
        ?User $user = null,
        ): array{
        
        $query = $this->createQueryBuilder('b');
        $expr = $query->expr();
        $and = $expr->andX();
        $and->add($expr->eq(1, 1));
        $query->where($and);

        // Фильтруем по публикациям пользователя (белый\черный список)
        if (!empty($user)){
            switch ($filter) {
                case BooksFilter::USER:
                    $and->add($expr->eq('b.uploader', ':userId'));
                    $query->setParameter('userId', $user->getId());
                    break;
                case BooksFilter::NONUSER:
                    $and->add($expr->neq('b.uploader', ':userId'));
                    $query->setParameter('userId', $user->getId());
                    break;        
                default:
                    break;
            }
        }
        
        // Фильтрация по строкам
        if (!empty($qname)){
            $and->add($expr->like('b.name', ":qname"));
            $query->setParameter('qname', "%$qname%");
        }
        if (!empty($qauthor)){
            $and->add($expr->like('b.author', ":qauthor"));
            $query->setParameter('qauthor', "%$qauthor%");
        }

        // Клонируемся для подсчета элементов
        $counterQuery = clone $query;
        $total = $counterQuery->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();
        
        // Формируем сам список
        $queryList = $query
        ->orderBy("b.$orderField", $orderDir)
        ->setMaxResults($limit)
        ->setFirstResult($offset)
        ->getQuery()->getResult();

        $resultList = array_map(function($book){
            if ($book instanceof Book);
            return [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'author' => $book->getAuthor(),
                'uploader' => $book->getUploader(),
                'hasCover' => !empty($book->getCoverPath()),
                'hasFile' => !empty($book->getFilePath()),
                'uploadDate' => $book->getUploadDate()?->format('d-m-Y'),
                'readDate' =>$book->getReadDate()?->format('d-m-Y'),
                'allowPublicDownload' => $book->getAllowDownload(),
            ];
        }, $queryList);

        return [
            'content' => $resultList,
            'totalElements' => $total,
        ];
    }    

    /**
     * Добавляет новую книгу
     *
     * @return array Массив ошибок записи файла на сервер 
     * @throws PathException Если невозможно прочесть записанный в БД файл
     **/
    public function addBook(
        string $name,
        ?string $author,
        ?UploadedFile $bookCover,
        ?UploadedFile $bookFile,
        User $uploader,
        ?DateTimeInterface $read_date,
        bool $allow_download = false,
    ): array {

        $book = new Book;
        $this->getEntityManager()->persist($book);

        if (empty($author)) $author = "Неизвестный";

        $book
        ->setName($name)
        ->setAuthor($author)
        ->setUploader($uploader)
        ->setReadDate($read_date)
        ->setUploadDate(new DateTimeImmutable("now"))
        ->setAllowDownload($allow_download);

        $fileErrors = [];
        try {
            $coverPath = null;
            if ($bookCover && $bookCover->isValid()){
                $coverPath = $this->uploader->saveFile($bookCover, $book->getId(), $book->getName());
                $book->setCoverPath($coverPath);
            }
        } catch (BadRequestException $e) {
            $fileErrors[] = $e->getMessage();
        } 

        try {
            $bookPath = null;
            if($bookFile && $bookFile->isValid()){
                $bookPath = $this->uploader->saveFile($bookFile, $book->getId(), $book->getName());
                $book->setFilePath($bookPath);
            }
        } catch (BadRequestException $e) {
            $fileErrors[] = $e->getMessage();
        }

        $this->getEntityManager()->flush();
        return $fileErrors;
    }

    /**
     * Редактирует поля книги в БД
     *
     * @return array Массив ошибок записи файла на сервер 
     * @throws PathException Если невозможно прочесть записанный в БД файл
     **/
     public function editBook(
        Book $book,
        ?string $name,
        ?string $author,
        ?UploadedFile $bookCover,
        ?UploadedFile $bookFile,
        ?DateTimeInterface $read_date,
        ?bool $allow_download = false,
        ) {
        if (!empty($name)){ $book->setName($name);}
        if (!empty($author)){ $book->setAuthor($author);}

        $fileErrors = [];
        if (!empty($bookCover)) {
            try {
                $newBookCover = $this->uploader->saveFile($bookCover, $book->getId(), $book->getName());
                if (!empty($book->getCoverPath()) && $newBookCover != $book->getCoverPath()){
                    $this->uploader->removeFile($book->getCoverPath());
                }
                $book->setCoverPath($newBookCover);
            } catch (BadRequestException $e) {
                $fileErrors[] = $e->getMessage();
            }
        }
   
        if (!empty($bookFile)) {
            try {
                $newBookFile = $this->uploader->saveFile($bookFile, $book->getId(), $book->getName());
                if (!empty($book->getFilePath()) && $newBookFile != $book->getFilePath()){
                    $this->uploader->removeFile($book->getFilePath());
                }
                $book->setFilePath($newBookFile);
            } catch (BadRequestException $e) {
                $fileErrors[] = $e->getMessage();
            }
        }

        if (!empty($read_date)) {
            $book->setReadDate($read_date);
        }
        if (!empty($allow_download)) {
            $book->setAllowDownload($allow_download);
        }
        $this->getEntityManager()->flush();
        return $fileErrors;
    }

    /**
     * Удаляет книгу из БД вместе с директорией хранения файлов
     *
     * @return void Массив ошибок записи файла на сервер 
     **/
    public function removeBook(
        Book $book
    ): void{
        $this->uploader->removeBookDir($book->getId());
        $this->getEntityManager()->remove($book);
        $this->getEntityManager()->flush();
    }

    public function loadCover(Book $book): File {
        return $this->uploader->loadFile($book->getCoverPath());
    }

    public function loadFile(Book $book): File {
        return $this->uploader->loadFile($book->getFilePath());
    }
}  

