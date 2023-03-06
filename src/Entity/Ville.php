<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $codePostal = null;

    #[ORM\OneToMany(mappedBy: 'ville', targetEntity: Lieu::class)]
    private Collection $listeLieux;

    public function __construct()
    {
        $this->listeLieux = new ArrayCollection();
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

    public function getCodePostal(): ?int
    {
        return $this->codePostal;
    }

    public function setCodePostal(int $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getListeLieux(): Collection
    {
        return $this->listeLieux;
    }

    public function addListeLieux(Lieu $listeLieux): self
    {
        if (!$this->listeLieux->contains($listeLieux)) {
            $this->listeLieux->add($listeLieux);
            $listeLieux->setVille($this);
        }

        return $this;
    }

    public function removeListeLieux(Lieu $listeLieux): self
    {
        if ($this->listeLieux->removeElement($listeLieux)) {
            // set the owning side to null (unless already changed)
            if ($listeLieux->getVille() === $this) {
                $listeLieux->setVille(null);
            }
        }

        return $this;
    }
}
