<?php
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity()]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /** MANAGER | RH | EMPLOYE */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $poste = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateEmbauche = null;

    #[ORM\Column(length: 20, options: ['default' => 'Actif'])]
    private string $statut = 'Actif';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Getters / Setters ---
    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getUserIdentifier(): string { return (string) $this->email; }
    public function getRoles(): array {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }
    /** Returns MANAGER | RH | EMPLOYE */
    public function getPrimaryRole(): string {
        if (in_array('ROLE_MANAGER', $this->roles)) return 'MANAGER';
        if (in_array('ROLE_RH', $this->roles))      return 'RH';
        return 'EMPLOYE';
    }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
    public function eraseCredentials(): void {}
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $v): static { $this->telephone = $v; return $this; }
    public function getDepartement(): ?string { return $this->departement; }
    public function setDepartement(?string $v): static { $this->departement = $v; return $this; }
    public function getPoste(): ?string { return $this->poste; }
    public function setPoste(?string $v): static { $this->poste = $v; return $this; }
    public function getDateEmbauche(): ?\DateTimeImmutable { return $this->dateEmbauche; }
    public function setDateEmbauche(?\DateTimeImmutable $v): static { $this->dateEmbauche = $v; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $v): static { $this->statut = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function toArray(): array {
        return [
            'id'           => $this->id,
            'email'        => $this->email,
            'prenom'       => $this->prenom,
            'nom'          => $this->nom,
            'role'         => $this->getPrimaryRole(),
            'telephone'    => $this->telephone,
            'departement'  => $this->departement,
            'poste'        => $this->poste,
            'statut'       => $this->statut,
            'dateEmbauche' => $this->dateEmbauche?->format('d/m/Y'),
            'initials'     => strtoupper(substr($this->prenom??'',0,1).substr($this->nom??'',0,1)),
        ];
    }
}
