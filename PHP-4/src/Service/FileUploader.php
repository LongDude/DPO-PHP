<?php
namespace App\Service;

use Exception;
use App\Entity\Book;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Dotenv\Exception\PathException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader {
    public static function saveFile(
        UploadedFile $file,
        Book $book,
        #[Autowire('%kernel.project_dir%/upload')]
        string $uploadPath = null,
    ): void {

        $fileType = BookFileType::Unknown;
        if (in_array($file->getMimeType(), ['image/jpeg', 'image/png'])){
            $fileType = BookFileType::Cover;
        } elseif ($file->getMimeType() == 'application/pdf'){
            $fileType = BookFileType::File;
        } else {
            throw new BadRequestException("File type not supported:". $file->getMimeType());
        }

        if ($file->getSize() > 5000000){
            throw new BadRequestException("File too big!: $uploadPath");
        }

        // Определяем новое имя файла
        $filename = match($fileType){
            BookFileType::Cover => $file->getBasename() . $file->getExtension(),
            BookFileType::File => preg_replace("/[^A-Za-z0-9._-]/", '_', $book->getName()) . $file->getExtension(),
        };

        // Действия по модификации файла
        if (is_dir($uploadPath) and preg_match('/.*\/upload$/', $uploadPath)){
            // Если аргумент - корень сервера файлов
            // Двухуровневое дерево - максимум 65536 элементов на каталог 
            $fileId = $book->getId();
            $clasterId = $fileId % (1 << 16); $fileId >>= 16;
            $bookDirId = $fileId % (1 << 16); $fileId >>= 16;
            $bookRelDir = implode('/', [$clasterId, $bookDirId]);
            $bookAbsDir = implode('/', [$uploadPath, $bookRelDir]);
            if (!file_exists($bookAbsDir)){
                mkdir($bookAbsDir, 0755, true);
            }

            // Сохраняем файл
            $file->move($bookAbsDir, $filename);
            $fileRelPath = implode('/', [$bookRelDir, $filename]);

            // Записываем новый путь в базу данных
            switch ($fileType){
                case BookFileType::Cover:
                    $book->setCoverPath($fileRelPath);
                    break;
                case BookFileType::File:
                    $book->setFilePath($fileRelPath);
                    break;
            }
        } elseif (is_file($uploadPath)) {
            // Если аргумент - существующий файл
            $file->move(
                dirname($uploadPath),
                basename($filename)
            );
        } else {
            throw new PathException("Unknown path: $uploadPath");
        }
    }

    public static function loadFile(
        string $server_filepath,
        #[Autowire('%kernel.project_dir%/upload')]
        string $uploadPath,
    ) :File {
        $absPath = implode('/', [$uploadPath, $server_filepath]);

        if (!file_exists($absPath)){
            throw new RuntimeException('File does not exists');
        }
        if (!is_readable($absPath)){
            throw new RuntimeException('File not readable');
        }

        return new File($absPath);
    }
}

enum BookFileType{
    case Unknown;
    case Cover;
    case File;
}
