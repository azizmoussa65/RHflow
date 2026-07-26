<?php
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Conge
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $employe = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;   // Annuel | Maladie | Maternité | Exceptionnel

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column]
    private ?int $nbJours = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motif = null;

    /** EN_ATTENTE | APPROUVE | REFUSE | EN_COURS */
    #[ORM\Column(length: 20, options: ['default' => 'EN_ATTENTE'])]
    private string $statut = 'EN_ATTENTE';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getEmploye(): ?User { return $this->employe; }
    public function setEmploye(?User $u): static { $this->employe = $u; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $v): static { $this->type = $v; return $this; }
    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $v): static { $this->dateDebut = $v; return $this; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(\DateTimeInterface $v): static { $this->dateFin = $v; return $this; }
    public function getNbJours(): ?int { return $this->nbJours; }
    public function setNbJours(int $v): static { $this->nbJours = $v; return $this; }
    public function getMotif(): ?string { return $this->motif; }
    public function setMotif(?string $v): static { $this->motif = $v; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $v): static { $this->statut = $v; return $this; }

    public function toArray(): array {
        return [
            'id'        => $this->id,
            'employe'   => $this->employe?->getNom().' '.$this->employe?->getPrenom(),
            'employeId' => $this->employe?->getId(),
            'initials'  => $this->employe ? strtoupper(substr($this->employe->getPrenom(),0,1).substr($this->employe->getNom(),0,1)) : '??',
            'type'      => $this->type,
            'dateDebut' => $this->dateDebut?->format('d/m/Y'),
            'dateFin'   => $this->dateFin?->format('d/m/Y'),
            'nbJours'   => $this->nbJours,
            'motif'     => $this->motif,
            'statut'    => $this->statut,
        ];
    }
}
