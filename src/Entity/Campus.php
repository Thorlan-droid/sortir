<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'campus', targetEntity: user::class)]
    private Collection $listeUtilisateurs;

    #[ORM\OneToMany(mappedBy: 'campus', targetEntity: Sortie::class)]
    private Collection $listeSortieCampus;

    public function __construct()
    {
        $this->listeUtilisateurs = new ArrayCollection();
        $this->listeSortieCampus = new ArrayCollection();
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

    /**
     * @return Collection<int, user>
     */
    public function getListeUtilisateurs(): Collection
    {
        return $this->listeUtilisateurs;
    }

    public function addListeUtilisateur(user $listeUtilisateur): self
    {
        if (!$this->listeUtilisateurs->contains($listeUtilisateur)) {
            $this->listeUtilisateurs->add($listeUtilisateur);
            $listeUtilisateur->setCampus($this);
        }

        return $this;
    }

    public function removeListeUtilisateur(user $listeUtilisateur): self
    {
        if ($this->listeUtilisateurs->removeElement($listeUtilisateur)) {
            // set the owning side to null (unless already changed)
            if ($listeUtilisateur->getCampus() === $this) {
                $listeUtilisateur->setCampus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getListeSortieCampus(): Collection
    {
        return $this->listeSortieCampus;
    }

    public function addListeSortieCampus(Sortie $listeSortieCampus): self
    {
        if (!$this->listeSortieCampus->contains($listeSortieCampus)) {
            $this->listeSortieCampus->add($listeSortieCampus);
            $listeSortieCampus->setCampus($this);
        }

        return $this;
    }

    public function removeListeSortieCampus(Sortie $listeSortieCampus): self
    {
        if ($this->listeSortieCampus->removeElement($listeSortieCampus)) {
            // set the owning side to null (unless already changed)
            if ($listeSortieCampus->getCampus() === $this) {
                $listeSortieCampus->setCampus(null);
            }
        }

        return $this;
    }
}
