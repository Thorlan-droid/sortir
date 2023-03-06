<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $rue = null;

    #[ORM\ManyToOne(inversedBy: 'listeLieux')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $ville = null;

    #[ORM\OneToMany(mappedBy: 'lieu', targetEntity: Sortie::class)]
    private Collection $listeSortieLieu;

    public function __construct()
    {
        $this->listeSortieLieu = new ArrayCollection();
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

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getListeSortieLieu(): Collection
    {
        return $this->listeSortieLieu;
    }

    public function addListeSortieLieu(Sortie $listeSortieLieu): self
    {
        if (!$this->listeSortieLieu->contains($listeSortieLieu)) {
            $this->listeSortieLieu->add($listeSortieLieu);
            $listeSortieLieu->setLieu($this);
        }

        return $this;
    }

    public function removeListeSortieLieu(Sortie $listeSortieLieu): self
    {
        if ($this->listeSortieLieu->removeElement($listeSortieLieu)) {
            // set the owning side to null (unless already changed)
            if ($listeSortieLieu->getLieu() === $this) {
                $listeSortieLieu->setLieu(null);
            }
        }

        return $this;
    }
}
