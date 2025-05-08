<?php

namespace App\Repository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use src\Entity\Book;
use Exception;

class BookRepository extends EntityRepository {
    /**
     * Для дополнительных методов
     */

     public function listBooks($page = 1, $limit = 12): array{
        $q = $this->createQueryBuilder('b')
        ->orderBy('b.upload_date', 'DESC')
        ->getQuery();

        $paginator = new Paginator($q);
        $paginator
        ->getQuery()
        ->setFirstResult($limit * ($page - 1))
        ->setMaxResults($limit);

        $books = iterator_to_array($paginator->getIterator());
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $limit);

        return [
            'data' => $books,
            'pagination' => [
                'total_limit' => $totalItems,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => (int) $pagesCount,
            ]
        ];
     }

     public function addBook(
         $name,
         $author,
         $cover_path,
         $file_path,
         $upload_date,
         $uploader,
         $read_date,
         $allow_download,
         ) {
     }

     public function editBook() {

     }
}