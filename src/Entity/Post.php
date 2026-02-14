<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'post')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le titre doit contenir au moins 3 caractères', maxMessage: 'Le titre ne peut dépasser 255 caractères')]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: 'Le contenu ne peut pas être vide')]
    #[Assert\Length(min: 10, max: 5000, minMessage: 'Le contenu doit contenir au moins 10 caractères', maxMessage: 'Le contenu ne peut dépasser 5000 caractères')]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private int $views = 0;

    #[ORM\Column(type: 'integer')]
    private int $likes = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(message: 'Veuillez saisir une URL d\'image valide.')] 
    private ?string $image = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $reactions = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Les tags ne peuvent dépasser 255 caractères')]
    #[Assert\Regex(pattern: '/^[\p{L}0-9 _,-]*$/u', message: 'Les tags ne doivent contenir que des lettres, chiffres, espaces, virgules, tirets et underscores.')]
    private ?string $tags = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $createdByAdmin = false;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'post', cascade: ['remove'])]
    private Collection $commentaires;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->commentaires = new ArrayCollection();
        $this->reactions = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;
        return $this;
    }

    public function incrementViews(): static
    {
        $this->views++;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;
        return $this;
    }

    public function incrementLikes(): static
    {
        $this->likes++;
        return $this;
    }

    public function decrementLikes(): static
    {
        if ($this->likes > 0) {
            $this->likes--;
        }
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getReactions(): ?array
    {
        return $this->reactions ?? [];
    }

    public function setReactions(?array $reactions): static
    {
        $this->reactions = $reactions;
        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setPost($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getPost() === $this) {
                $commentaire->setPost(null);
            }
        }
        return $this;
    }

    public function getReactionCounts(): array
    {
        $counts = [
            'like' => 0,
            'love' => 0,
            'haha' => 0,
            'wow' => 0,
            'sad' => 0,
            'angry' => 0,
        ];

        if (is_array($this->reactions)) {
            foreach ($this->reactions as $reaction) {
                if (is_array($reaction) && isset($reaction['type']) && isset($counts[$reaction['type']])) {
                    $counts[$reaction['type']]++;
                }
            }
        }

        return $counts;
    }

    public function getTotalReactions(): int
    {
        return count($this->reactions ?? []);
    }

    public function isCreatedByAdmin(): bool
    {
        return $this->createdByAdmin;
    }

    public function setCreatedByAdmin(bool $createdByAdmin): static
    {
        $this->createdByAdmin = $createdByAdmin;
        return $this;
    }
}
