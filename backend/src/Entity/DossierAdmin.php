<?php
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class DossierAdmin
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $employe = null;

    #[ORM\Column(length: 200)]
    private ?string $titre = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;   // Diplôme | Certificat | Attestation | Autre

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierPath = null;

    /** Validé | En attente | Refusé */
    #[ORM\Column(length: 20, options: ['default' => 'En attente'])]
    private string $statut = 'En attente';

    #[ORM\Column]
    private \DateTimeImmutable $dateAjout;

    public function __construct() { $this->dateAjout = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getEmploye(): ?User { return $this->employe; }
    public function setEmploye(?User $u): static { $this->employe = $u; return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $v): static { $this->titre = $v; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $v): static { $this->type = $v; return $this; }
    public function getFichierPath(): ?string { return $this->fichierPath; }
    public function setFichierPath(?string $v): static { $this->fichierPath = $v; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $v): static { $this->statut = $v; return $this; }
    public function getDateAjout(): \DateTimeImmutable { return $this->dateAjout; }

    public function toArray(): array {
        $ext = pathinfo($this->fichierPath ?? '', PATHINFO_EXTENSION);
        return [
            'id'        => $this->id,
            'titre'     => $this->titre,
            'type'      => $this->type,
            'employe'   => $this->employe?->getPrenom().' '.$this->employe?->getNom(),
            'employeId' => $this->employe?->getId(),
            'fichier'   => in_array(strtolower($ext), ['png','jpg','jpeg']) ? 'img' : 'pdf',
            'dateAjout' => $this->dateAjout->format('d M Y'),
            'statut'    => $this->statut,
        ];
    }
}
