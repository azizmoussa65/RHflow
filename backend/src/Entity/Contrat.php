<?php
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Contrat
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $employe = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;   // CDI | CDD | Stage

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $salaire = null;

    #[ORM\Column(length: 30, options: ['default' => 'Actif'])]
    private string $statut = 'Actif';

    public function getId(): ?int { return $this->id; }
    public function getEmploye(): ?User { return $this->employe; }
    public function setEmploye(?User $u): static { $this->employe = $u; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $v): static { $this->type = $v; return $this; }
    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $v): static { $this->dateDebut = $v; return $this; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(?\DateTimeInterface $v): static { $this->dateFin = $v; return $this; }
    public function getSalaire(): ?string { return $this->salaire; }
    public function setSalaire(string $v): static { $this->salaire = $v; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $v): static { $this->statut = $v; return $this; }

    public function toArray(): array {
        return [
            'id'        => $this->id,
            'employe'   => $this->employe?->getPrenom().' '.$this->employe?->getNom(),
            'employeId' => $this->employe?->getId(),
            'initials'  => $this->employe ? strtoupper(substr($this->employe->getPrenom(),0,1).substr($this->employe->getNom(),0,1)) : '??',
            'type'      => $this->type,
            'dateDebut' => $this->dateDebut?->format('d/m/Y'),
            'dateFin'   => $this->dateFin?->format('d/m/Y') ?? null,
            'salaire'   => number_format((float)$this->salaire, 0, '.', ' '),
            'statut'    => $this->statut,
            'expireBientot' => $this->isExpireBientot(),
        ];
    }

    private function isExpireBientot(): bool {
        if (!$this->dateFin) return false;
        $diff = (new \DateTimeImmutable())->diff($this->dateFin);
        return !$diff->invert && $diff->days <= 30;
    }
}
