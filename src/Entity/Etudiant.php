<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtudiantRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class Etudiant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La filière est obligatoire.')]
    private ?string $filiere = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le thème du mémoire est obligatoire.')]
    private ?string $themeMemoire = null;

    #[ORM\OneToOne(mappedBy: 'etudiant', cascade: ['persist', 'remove'])]
    private ?Soutenance $soutenance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
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

    public function getFiliere(): ?string
    {
        return $this->filiere;
    }

    public function setFiliere(string $filiere): static
    {
        $this->filiere = $filiere;
        return $this;
    }

    public function getThemeMemoire(): ?string
    {
        return $this->themeMemoire;
    }

    public function setThemeMemoire(string $themeMemoire): static
    {
        $this->themeMemoire = $themeMemoire;
        return $this;
    }

    public function getSoutenance(): ?Soutenance
    {
        return $this->soutenance;
    }

    public function setSoutenance(?Soutenance $soutenance): static
    {
        $this->soutenance = $soutenance;
        return $this;
    }
}