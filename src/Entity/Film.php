<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FilmRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getFilms', 'getDirectors'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getFilms', 'getDirectors'])]
    #[Assert\NotBlank(message: "Ceci est un message qui vient de la validation")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le titre doit faire au moins {{ limit }} caractères", 
    maxMessage: "Le titre ne peut pas faire plus de {{ limit }} caractères")]

    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getFilms', 'getDirectors'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getFilms', 'getDirectors'])]
    private ?string $photo = null;

    #[ORM\ManyToOne(inversedBy: 'films', cascade:["remove", "persist"])]
    #[Groups(['getFilms'])]
    private ?Director $director = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDirector(): ?Director
    {
        return $this->director;
    }

    public function setDirector(?Director $director): self
    {
        $this->director = $director;

        return $this;
    }
}
