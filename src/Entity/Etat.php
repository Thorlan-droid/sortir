<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Choice(["creee", "ouverte", "cloturee", "activite en cours", "passee", "annulee"])]
    #[ORM\Column(length: 50)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'etat', targetEntity: sortie::class)]
    private Collection $etatSortie;

    public function __construct()
    {
        $this->etatSortie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, sortie>
     */
    public function getEtatSortie(): Collection
    {
        return $this->etatSortie;
    }

    public function addEtatSortie(sortie $etatSortie): self
    {
        if (!$this->etatSortie->contains($etatSortie)) {
            $this->etatSortie->add($etatSortie);
            $etatSortie->setEtat($this);
        }

        return $this;
    }

    public function removeEtatSortie(sortie $etatSortie): self
    {
        if ($this->etatSortie->removeElement($etatSortie)) {
            // set the owning side to null (unless already changed)
            if ($etatSortie->getEtat() === $this) {
                $etatSortie->setEtat(null);
            }
        }

        return $this;
    }
}
