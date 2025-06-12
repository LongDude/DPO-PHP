<?php
namespace App\Service;

use Exception;
use App\Entity\Book;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
use Symfony\Component\Dotenv\Exception\PathException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader {

    const MAX_SIZE = 50_000_000;

    public function __construct(
        #[Autowire('%kernel.project_dir%/uploads')]
        private string $uploadPath
    ){
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0775, true);
        }
    }

    public function saveFile(
        UploadedFile $file,
        int $bookId,
        string $bookName
    ): string {
        $fileType = BookFileType::Unknown;
        if (in_array($file->getMimeType(), ['image/jpeg', 'image/png'])){
            $fileType = BookFileType::Cover;
        } elseif ($file->getMimeType() == 'application/pdf'){
            $fileType = BookFileType::File;
        } else {
            throw new BadRequestException("File type not supported:". $file->getMimeType());
        }

        if ($file->getSize() > FileUploader::MAX_SIZE){
            throw new BadRequestException("File too big!: ".$this->uploadPath);
        }

        // Определяем новое имя файла
        $filename = match($fileType){
            BookFileType::Cover => $file->getBasename() .".". $file->getClientOriginalExtension(),
            BookFileType::File => preg_replace("/[^A-Za-z0-9а-яА-Я._-]/", '_', $bookName) . "." . $file->getClientOriginalExtension(),
        };

        // Двухуровневое дерево - максимум 65536 элементов на каталог 
        $fileId = $bookId;
        $clasterId = $fileId % (1 << 16); $fileId >>= 16;
        $bookDirId = $fileId % (1 << 16); $fileId >>= 16;
        $bookRelDir = implode('/', [$clasterId, $bookDirId]);
        $bookAbsDir = implode('/', [$this->uploadPath, $bookRelDir]);
        if (!file_exists($bookAbsDir)){
            mkdir($bookAbsDir, 0755, true);
        }
        
        // Сохраняем файл
        $file->move($bookAbsDir, $filename);
        $fileRelPath = implode('/', [$bookRelDir, $filename]);
        
        // Записываем новый путь в базу данных
        return $fileRelPath;
    }

    public function loadFile(
        string $server_filepath,
    ) :File {
        $absPath = implode('/', [$this->uploadPath, $server_filepath]);

        if (!file_exists($absPath)){
            throw new RuntimeException('File does not exists');
        }
        if (!is_readable($absPath)){
            throw new RuntimeException('File not readable');
        }

        return new File($absPath);
    }

    public function removeFile(
        string $server_filepath
    ) :void {
        $absPath = implode('/', [$this->uploadPath, $server_filepath]);
        if (!file_exists($absPath)){
            return;
        }
        unlink($absPath);
    }

    public function removeBookDir(
        int $bookId
    ) :void {
        $fileId = $bookId;
        $clasterId = $fileId % (1 << 16); $fileId >>= 16;
        $bookDirId = $fileId % (1 << 16); $fileId >>= 16;
        $bookRelDir = implode('/', [$clasterId, $bookDirId]);
        $bookAbsDir = implode('/', [$this->uploadPath, $bookRelDir]);
        
        if (is_dir($bookAbsDir)){
            array_map('unlink', glob("$bookAbsDir/*"));
            rmdir($bookAbsDir);
        }
    }
}

enum BookFileType{
    case Unknown;
    case Cover;
    case File;
}
