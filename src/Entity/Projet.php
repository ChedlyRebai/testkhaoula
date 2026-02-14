<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du projet est requis.")]
    private ?string $nom = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Assert\Length(min: 10, minMessage: "La description doit contenir au moins {{ limit }} caractères.")]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $enabled = true;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Tache::class, cascade: ['remove'])]
    private Collection $taches;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->enabled = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getTaches(): Collection
    {
        return $this->taches;
    }

    public function addTache(Tache $tache): self
    {
        if (!$this->taches->contains($tache)) {
            $this->taches->add($tache);
            $tache->setProjet($this);
        }
        return $this;
    }

    public function removeTache(Tache $tache): self
    {
        if ($this->taches->removeElement($tache)) {
            if ($tache->getProjet() === $this) {
                $tache->setProjet(null);
            }
        }
        return $this;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}