<?php

namespace App\Entity;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass:BookRepository::class)]
#[ORM\Table(name: 'books')]
class Book {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'bigint')]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length:100, unique: false, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'author', type: 'string', length:100, unique: false, nullable: true)]
    private ?string $author;

    #[ORM\Column(name: 'cover_path', type: 'string', unique: false, nullable: true)]
    private ?string $cover_path;

    #[ORM\Column(name: 'file_path', type: 'string', unique: false, nullable: true)]
    private ?string $file_path;
    
    #[ORM\Column(name:'upload_date', type: 'date', nullable: false, options: ['default' => 'CURRENT_DATE'])]
    private DateTimeInterface $upload_date;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'books')]
    #[ORM\JoinColumn(name: 'uploader_id', referencedColumnName: 'id')]
    private User $uploader;

    #[ORM\Column(name: 'read_date' ,type: 'date', nullable: true)]
    private ?DateTimeInterface $read_date;
    
    #[ORM\Column(name: 'allow_download', type: 'boolean', options:['default' => false])]
    private bool $allow_download = false;

    public function getId ():  int{ return $this->id; }
    public function getName ():  string{ return $this->name; }
    public function getAuthor ():  ?string{ return $this->author; }
    public function getCoverPath ():  ?string{ return $this->cover_path; }
    public function getFilePath ():  ?string{ return $this->file_path; }
    public function getUploadDate ():  DateTimeInterface{ return $this->upload_date; }
    public function getUploader ():  User{ return $this->uploader; }
    public function getReadDate ():  ?DateTimeInterface{ return $this->read_date; }
    public function getAllowDownload ():  bool{ return $this->allow_download; }

    public function setName (string $name): self {$this->name = $name; return $this; }
    public function setAuthor (string $author): self {$this->author = $author; return $this; }
    public function setCoverPath (string $cover_path): self {$this->cover_path = $cover_path; return $this; }
    public function setFilePath (string $file_path): self {$this->file_path = $file_path; return $this; }
    public function setUploadDate (DateTimeInterface $upload_date): self {$this->upload_date = $upload_date; return $this; }
    public function setUploader (User $uploader): self {$this->uploader = $uploader; return $this; }
    public function setReadDate (DateTimeInterface $read_date): self {$this->read_date = $read_date; return $this; }
    public function setAllowDownload (bool $allow_download): self {$this->allow_download = $allow_download; return $this; }

}