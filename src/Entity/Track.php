<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]

    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['read','create','update'])]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read','create','update'])]
    private ?int $duration = null;

    #[ORM\ManyToOne(inversedBy: 'tracks')]
    private ?Album $album = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }
}
