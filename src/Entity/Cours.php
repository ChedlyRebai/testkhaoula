<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column(name: "id_cours", type: "integer")] private ?int $id_cours = null;

    #[ORM\Column(name: "titre", length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length( min: 5, max: 100, minMessage: "Le titre doit contenir au moins {{ limit }} caractères", maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères" )]
    #[Assert\Regex( pattern: "/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/", message: "Le titre doit contenir uniquement des lettres" )]
 private ?string $titre = null;

    #[ORM\Column(name: "description",type: "text")]
     #[Assert\NotBlank(message: "La description est obligatoire")]
      #[Assert\Length( min: 20, minMessage: "La description doit contenir au moins {{ limit }} caractères" )]
       #[Assert\Regex( pattern: "/^[A-Za-zÀ-ÖØ-öø-ÿ\s.,;:!?'-]+$/", message: "La description doit contenir uniquement des lettres et ponctuations simples" )]
    private ?string $description = null;

   
    #[ORM\Column(name: "date_publication", type: "datetime")] 
    #[Assert\NotNull(message: "La date de publication est obligatoire")]
     #[Assert\GreaterThanOrEqual("today", message: "La date de publication ne peut pas être dans le passé")]
    private ?\DateTime $date_publication = null;

    #[ORM\Column(name: "date_creation", type: "datetime", nullable: true)]
     private ?\DateTime $date_creation = null;


    #[ORM\Column(name: "visibilite",type: "boolean")]
    #[Assert\NotNull(message: "La visibilité doit être précisée")]
    private ?bool $visibilite = null;

    
    #[ORM\Column(name: "contenu", type: "text", nullable: true)] 
    #[Assert\Length( min: 5, minMessage: "Le contenu doit contenir au moins {{ limit }} caractères" )]
    private ?string $contenu = null;

    #[ORM\Column(name: "type_contenu", length: 100, nullable: true)]
    #[Assert\Choice( choices: ["pdf", "video", "texte"], message: "Le type de contenu doit être pdf, video ou texte" )]
    private ?string $type_contenu = null;

    #[ORM\Column(name: "url_contenu", length: 255, nullable: true)]
    #[Assert\Url(message: "L’URL doit être valide")]
    private ?string $url_contenu = null;

    /**
     * @var Collection<int, Quiz>
     */
    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'relation')]
    private Collection $quizzes;

    public function __construct()
    {
        $this->quizzes = new ArrayCollection();
    }



    public function getIdCours(): ?int
    {
        return $this->id_cours;
    }

    public function setIdCours(int $id_cours): static
    {
        $this->id_cours = $id_cours;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDatePublication(): ?\DateTime
    {
        return $this->date_publication;
    }

    public function setDatePublication(\DateTime $date_publication): static
    {
        $this->date_publication = $date_publication;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function isVisibilite(): ?bool
    {
        return $this->visibilite;
    }

    public function setVisibilite(bool $visibilite): static { $this->visibilite = $visibilite; return $this; }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getTypeContenu(): ?string
    {
        return $this->type_contenu;
    }

    public function setTypeContenu(?string $type_contenu): static
    {
        $this->type_contenu = $type_contenu;

        return $this;
    }

    public function getUrlContenu(): ?string
    {
        return $this->url_contenu;
    }

    public function setUrlContenu(?string $url_contenu): static
    {
        $this->url_contenu = $url_contenu;

        return $this;
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setRelation($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            // set the owning side to null (unless already changed)
            if ($quiz->getRelation() === $this) {
                $quiz->setRelation(null);
            }
        }

        return $this;
    }
}

