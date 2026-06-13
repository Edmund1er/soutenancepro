<?php

namespace App\Entity;

use App\Repository\SoutenanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SoutenanceRepository::class)]
#[ORM\UniqueConstraint(name: "unique_etudiant", columns: ["etudiant_id"])]
class Soutenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date est obligatoire.')]
    #[Assert\GreaterThanOrEqual(value: 'today', message: 'La date doit être aujourd\'hui ou dans le futur.')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank(message: 'L\'heure est obligatoire.')]
    private ?\DateTimeInterface $heure = null;

    #[ORM\OneToOne(inversedBy: 'soutenance')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'L\'étudiant est obligatoire.')]
    private ?Etudiant $etudiant = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'La salle est obligatoire.')]
    private ?Salle $salle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Le président est obligatoire.')]
    private ?Enseignant $president = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Le rapporteur est obligatoire.')]
    private ?Enseignant $rapporteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'L\'examinateur est obligatoire.')]
    private ?Enseignant $examinateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;
        return $this;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(Etudiant $etudiant): static
    {
        $this->etudiant = $etudiant;
        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(Salle $salle): static
    {
        $this->salle = $salle;
        return $this;
    }

    public function getPresident(): ?Enseignant
    {
        return $this->president;
    }

    public function setPresident(Enseignant $president): static
    {
        $this->president = $president;
        return $this;
    }

    public function getRapporteur(): ?Enseignant
    {
        return $this->rapporteur;
    }

    public function setRapporteur(Enseignant $rapporteur): static
    {
        $this->rapporteur = $rapporteur;
        return $this;
    }

    public function getExaminateur(): ?Enseignant
    {
        return $this->examinateur;
    }

    public function setExaminateur(Enseignant $examinateur): static
    {
        $this->examinateur = $examinateur;
        return $this;
    }
}