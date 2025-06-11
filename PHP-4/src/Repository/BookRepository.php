<?php

namespace App\Repository;
use App\Service\FileUploader;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Book;
use App\Entity\User;
use Exception;
use Symfony\Component\Dotenv\Exception\PathException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookRepository extends EntityRepository {


    /**
     * Возвращает список книг с учетом фильтров и пагинации
     * @return void
     */
    public function listBooks(int $limit, int $offset): array{
        $query = $this->createQueryBuilder('b')
            ->select('b')
            ->orderBy('b.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        return $query->getQuery()->getResult();
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

        $book = $this->findOneBy(['name' => $name]);
        if (!$book){
            $book = new Book;
            $this->getEntityManager()->persist($book);
        }

        $book
        ->setName($name)
        ->setAuthor($author)
        ->setUploader($uploader)
        ->setReadDate($read_date)
        ->setAllowDownload($allow_download);

        $fileErrors = [];
        try {
            $bookCover && $bookCover->isValid() && FileUploader::saveFile($bookCover, $book);
        } catch (BadRequestException $e) {
            $fileErrors[] = $e->getMessage();
        } 

        try {
            $bookFile && $bookFile->isValid() && FileUploader::saveFile($bookFile, $book);
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
                FileUploader::saveFile($bookCover, $book);
            } catch (BadRequestException $e) {
                $fileErrors[] = $e->getMessage();
            }
        }
   
        if (!empty($bookFile)) {
            try {
                FileUploader::saveFile($bookFile, $book);
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
}  