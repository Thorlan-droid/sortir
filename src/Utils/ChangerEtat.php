<?php

namespace App\Utils;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateInterval;

class ChangerEtat
{

    public function __construct(private EtatRepository $etatRepository, private SortieRepository $sortieRepository)
    {
    }

//    public function verifierEtat(Sortie $sortie): Sortie
//    {
//        $this->changerEtat($sortie);
//
//        return $sortie;
//
//    }
//
//    public function verifierEtats(array $sorties): array
//    {
//
//        foreach ($sorties as $sortie) {
//
//            $this->changerEtat($sortie);
//        }
//        return $sorties;
//    }


    public function changerEtat(Sortie $sortie)
    {

        if ($sortie->getDateHeureDebut() === null) {
            throw new \Exception('DateHeureDebut cannot be null');
        }

        $now = new \DateTime('now');
        $heureDebut = clone $sortie->getDateHeureDebut();
        $heureDebut2 = clone $sortie->getDateHeureDebut();
        $dateInscription = $sortie->getDateLimiteInscription();
        $nbParticipants = $sortie->getInscrits()->count();
        $nbInscriptionMax = $sortie->getNbInscriptionMax();
        $dateHeureFin = date_add($heureDebut,
            DateInterval::createFromDateString($sortie->getDuree() . 'minutes'));
        $dateHistory = date_add($heureDebut2,
            DateInterval::createFromDateString($sortie->getDuree() + 43200 . 'minutes'));

        if ($sortie->getEtat()->getLibelle() != 'Créée' &&
            $sortie->getEtat()->getLibelle() != 'Annulée') {
            if ($sortie->getDateHeureDebut() > $now &&
                $dateInscription > $now &&
                $nbParticipants < $nbInscriptionMax) {
                $this->setEtatSortie($sortie, 2);
            } elseif ($sortie->getDateHeureDebut() > $now &&
                ($dateInscription < $now ||
                    $nbParticipants >= $nbInscriptionMax)) {
                $this->setEtatSortie($sortie, 3);
            } elseif ($sortie->getDateHeureDebut() < $now &&
                $dateHeureFin > $now) {
                $this->setEtatSortie($sortie, 4);
            } elseif ($dateHeureFin < $now &&
                $dateHistory > $now) {
                $this->setEtatSortie($sortie, 5);
            } elseif ($dateHistory < $now) {
                $this->setEtatSortie($sortie, 7);
            }
        } else {
            if ($dateHeureFin < $now &&
                $dateHistory > $now) {
                $this->setEtatSortie($sortie, 5);
            } elseif ($dateHistory <= $now) {
                $this->setEtatSortie($sortie, 7);
            }
        }
    }

    private function setEtatSortie(Sortie $sortie, int $etatId)
    {
        $sortie->setEtat($this->etatRepository->find($etatId));
        $this->sortieRepository->save($sortie, true);
    }

}
