<?php
namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Projet
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $categorie = null;

    #[ORM\Column(length: 30, options: ['default' => 'En cours'])]
    private string $statut = 'En cours';

    #[ORM\Column(options: ['default' => 0])]
    private int $avancement = 0;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\Column(length: 20, options: ['default' => '#3b82f6'])]
    private string $couleur = '#3b82f6';

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'projet_membre')]
    private Collection $membres;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->membres   = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $v): static { $this->nom = $v; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(string $v): static { $this->categorie = $v; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $v): static { $this->statut = $v; return $this; }
    public function getAvancement(): int { return $this->avancement; }
    public function setAvancement(int $v): static { $this->avancement = $v; return $this; }
    public function getDeadline(): ?\DateTimeInterface { return $this->deadline; }
    public function setDeadline(?\DateTimeInterface $v): static { $this->deadline = $v; return $this; }
    public function getCouleur(): string { return $this->couleur; }
    public function setCouleur(string $v): static { $this->couleur = $v; return $this; }
    public function getMembres(): Collection { return $this->membres; }
    public function addMembre(User $u): static { if (!$this->membres->contains($u)) $this->membres->add($u); return $this; }
    public function removeMembre(User $u): static { $this->membres->removeElement($u); return $this; }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'description' => $this->description,
            'categorie'   => $this->categorie,
            'statut'      => $this->statut,
            'avancement'  => $this->avancement,
            'deadline'    => $this->deadline?->format('d M'),
            'couleur'     => $this->couleur,
            'membres'     => $this->membres->map(fn(User $u) => ['initials' => strtoupper(substr($u->getPrenom(),0,1).substr($u->getNom(),0,1))])->toArray(),
        ];
    }
}
