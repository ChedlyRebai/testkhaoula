<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
 use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_quiz = null;


    #[ORM\Column(length: 255)]
        #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length( min: 5, max: 100, minMessage: "Le titre doit contenir au moins {{ limit }} caractères", maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères" )]
    #[Assert\Regex( pattern: "/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/", message: "Le titre doit contenir uniquement des lettres" )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
         #[Assert\NotBlank(message: "La description est obligatoire")]
      #[Assert\Length( min: 20, minMessage: "La description doit contenir au moins {{ limit }} caractères" )]
       #[Assert\Regex( pattern: "/^[A-Za-zÀ-ÖØ-öø-ÿ\s.,;:!?'-]+$/", message: "La description doit contenir uniquement des lettres et ponctuations simples" )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Les questions sont obligatoires")]
        #[Assert\Count( min: 3, max: 20, minMessage: "Un quiz doit contenir au moins {{ limit }} questions", maxMessage: "Un quiz ne peut pas contenir plus de {{ limit }} questions" )]
    private array $questions = [];

    #[ORM\Column]
    private ?\DateTime $date_creation = null;

    #[ORM\Column]
        #[Assert\NotNull(message: "La date de publication est obligatoire")]
     #[Assert\GreaterThanOrEqual("today", message: "La date de publication ne peut pas être dans le passé")]
    private ?\DateTime $date_echeance = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "La durée doit être un nombre positif")]
    private ?int $duree = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le score maximum est obligatoire")] 
    #[Assert\Positive(message: "Le score maximum doit être un nombre positif")]
    private ?int $score_max = null;

#[ORM\Column] #[Assert\NotBlank(message: "Le nombre de tentatives est obligatoire")] #[Assert\Positive(message: "Le nombre de tentatives doit être positif")] #[Assert\Range( min: 1, max: 3, notInRangeMessage: "Le nombre de tentatives doit être entre {{ min }} et {{ max }}" )]
    private ?int $tentatives = null;
    // Relation vers Cours
     #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'quizzes')] 
     #[ORM\JoinColumn(name: "id_cours", referencedColumnName: "id_cours", nullable: false)]
      private ?Cours $relation = null;
      public function __construct() { 
        $this->date_creation = new \DateTime(); 
         }

    public function getId(): ?int
    {
        return $this->id_quiz;
    }

    public function getIdQuiz(): ?int
    {
        return $this->id_quiz;
    }

    public function setIdQuiz(int $id_quiz): static
    {
        $this->id_quiz = $id_quiz;

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

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function setQuestions(array $questions): static
    {
        $this->questions = $questions;

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

    public function getDateEcheance(): ?\DateTime
    {
        return $this->date_echeance;
    }

    public function setDateEcheance(\DateTime $date_echeance): static
    {
        $this->date_echeance = $date_echeance;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getScoreMax(): ?int
    {
        return $this->score_max;
    }

    public function setScoreMax(int $score_max): static
    {
        $this->score_max = $score_max;

        return $this;
    }

    public function getTentatives(): ?int
     { return $this->tentatives; } 

     public function setTentatives(int $tentatives): static 
     { $this->tentatives = $tentatives; return $this; }

    public function getRelation(): ?Cours
    {
        return $this->relation;
    }

    public function setRelation(?Cours $relation): static
    {
        $this->relation = $relation;

        return $this;
    }
}
