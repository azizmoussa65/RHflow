<?php
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Evaluation
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $employe = null;

    #[ORM\Column(length: 100)]
    private ?string $periode = null;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1)]
    private ?string $competences = null;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1)]
    private ?string $teamwork = null;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1)]
    private ?string $initiative = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1)]
    private ?string $noteGlobale = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getEmploye(): ?User { return $this->employe; }
    public function setEmploye(?User $u): static { $this->employe = $u; return $this; }
    public function getPeriode(): ?string { return $this->periode; }
    public function setPeriode(string $v): static { $this->periode = $v; return $this; }
    public function getCompetences(): ?string { return $this->competences; }
    public function setCompetences(string $v): static { $this->competences = $v; return $this; }
    public function getTeamwork(): ?string { return $this->teamwork; }
    public function setTeamwork(string $v): static { $this->teamwork = $v; return $this; }
    public function getInitiative(): ?string { return $this->initiative; }
    public function setInitiative(string $v): static { $this->initiative = $v; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $v): static { $this->commentaire = $v; return $this; }
    public function getNoteGlobale(): ?string { return $this->noteGlobale; }
    public function setNoteGlobale(string $v): static { $this->noteGlobale = $v; return $this; }

    public function computeGlobalNote(): void {
        $sum = (float)$this->competences + (float)$this->teamwork + (float)$this->initiative;
        $this->noteGlobale = round($sum / 3, 1);
    }

    public function toArray(): array {
        return [
            'id'         => $this->id,
            'employe'    => $this->employe?->getPrenom().' '.$this->employe?->getNom(),
            'employeId'  => $this->employe?->getId(),
            'initials'   => $this->employe ? strtoupper(substr($this->employe->getPrenom(),0,1).substr($this->employe->getNom(),0,1)) : '??',
            'periode'    => $this->periode,
            'competences'=> (float)$this->competences,
            'teamwork'   => (float)$this->teamwork,
            'initiative' => (float)$this->initiative,
            'noteGlobale'=> (float)$this->noteGlobale,
            'commentaire'=> $this->commentaire,
        ];
    }
}
