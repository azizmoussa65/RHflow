<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Tache
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private string $titre = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20, options: ['default' => 'À faire'])]
    private string $statut = 'À faire';

    #[ORM\Column(length: 20, options: ['default' => 'Normale'])]
    private string $priorite = 'Normale';

    #[ORM\ManyToOne(targetEntity: Projet::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Projet $projet = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $assigneA = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $v): static { $this->titre = $v; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $v): static { $this->statut = $v; return $this; }
    public function getPriorite(): string { return $this->priorite; }
    public function setPriorite(string $v): static { $this->priorite = $v; return $this; }
    public function getProjet(): ?Projet { return $this->projet; }
    public function setProjet(?Projet $v): static { $this->projet = $v; return $this; }
    public function getAssigneA(): ?User { return $this->assigneA; }
    public function setAssigneA(?User $v): static { $this->assigneA = $v; return $this; }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'titre'       => $this->titre,
            'description' => $this->description,
            'statut'      => $this->statut,
            'priorite'    => $this->priorite,
            'projetId'    => $this->projet?->getId(),
            'assigneA'    => $this->assigneA ? [
                'id'       => $this->assigneA->getId(),
                'nom'      => $this->assigneA->getPrenom().' '.$this->assigneA->getNom(),
                'initials' => strtoupper(substr($this->assigneA->getPrenom(),0,1).substr($this->assigneA->getNom(),0,1)),
                'poste'    => $this->assigneA->getPoste(),
            ] : null,
        ];
    }
}
