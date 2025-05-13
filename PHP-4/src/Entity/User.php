<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass:UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'bigint')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Constraints\Email]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    #[Constraints\Length(min: 4, minMessage: "Пароль должен состоять минимум из 4 символов")]
    private string $password;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'uploader')]
    private Collection $books;

    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function getPassword(): ?string { return $this->password; }
    public function getRoles(): array { return $this->roles; }
    public function getBooks(): array {return $this->books; }


    public function setEmail($email): self {
        $this->email = $email;
        return $this;
    }
    public function setPassword($password): self {
        $this->password = $password;
        return $this;
    }
    public function setRoles($roles): self {
        $this->roles = $roles;
        return $this;
    }

    // Уголок интерфейсов авторизации Symfony
    public function eraseCredentials(): void {}
    public function getUserIdentifier(): string {
        return $this->email;
    }
}