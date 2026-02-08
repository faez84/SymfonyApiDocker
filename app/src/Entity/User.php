<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:list', 'user:profile'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(
        message: 'Email is required.',
    )]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[Groups(['user:create', 'user:update', 'user:list', 'user:profile'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:list', 'user:profile'])]

    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:create', 'user:password_update'])]
    #[Assert\NotBlank(
        message: 'Password is required.',
        groups: ['user:create', 'user:password_update']
    )]
    #[Assert\Length(
        min: 8,
        max: 32,
        minMessage: 'Password must be at least {{ limit }} characters long.',
        groups: ['user:create', 'user:password_update']
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/'
    )]


    private ?string $password = null;

    #[Groups(['user:password_update'])]
    #[Assert\NotBlank(
        message: 'newPassword is required.',
        groups: ['user:password_update']
    )]
    #[Assert\Length(
        min: 8,
        max: 32,
        minMessage: 'New Password must be at least {{ limit }} characters long.',
        groups: ['user:password_update']
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/'
    )]


    private ?string $newPassword = null;

    #[Assert\Expression(
        'this.getNewPassword() === null or this.getPasswordConfirmation() === this.getNewPassword()',
        message: 'Password confirmation does not match.',
        groups: ['user:password_update']
    )]
    #[Groups(['user:password_update'])]
    #[Assert\NotBlank(
        message: 'passwordConfirmation is required.',
        groups: ['user:password_update']
    )]
    private ?string $passwordConfirmation = null;
    #[Groups(['user:list', 'user:profile'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        mimeTypesMessage: 'Please upload a valid image file.'
    )]
    private ?UploadedFile $photoFile = null;

    public function getPasswordConfirmation(): ?string
    {
        return $this->passwordConfirmation;
    }
    public function setPasswordConfirmation(?string $passwordConfirmation): static
    {
        $this->passwordConfirmation = $passwordConfirmation;
        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): static
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPhotoFile(): ?UploadedFile
    {
        return $this->photoFile;
    }
    public function setPhotoFile(?UploadedFile $file): self
    {
        $this->photoFile = $file;
        return $this;
    }
}
