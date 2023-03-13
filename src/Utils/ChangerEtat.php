<?php

namespace App\Utils;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;

class ChangerEtat
{
//    protected $sortieRepository;
//    protected $etatRepository;
//    protected $entityManager;

    /**
     * @param sortieRepository
     * @param $etatRepository
     * @param $entityManager
     */

    public function __construct(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager)
    {
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->entityManager = $entityManager;
    }

    public function verifierEtat(SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request): void
    {
        $sorties = $this->sortieRepository->findAll();
        $dateActuelle = new \DateTime();
        $dateActuelle->modify('+ 1 hour');

        foreach ($sorties as $sortie) {
            if ($sortie->getEtat() != 'Annulée' and $sortie->getEtat() != 'Créée') {

                $dateHeureDebut = $sortie->getDateHeureDebut();
                if ($sortie->getNbInscriptionMax() == $sortie->getInscrits()->count() or $sortie->getDateLimiteInscription() <= $dateActuelle) {
                    $sortie->setEtat(($this->etatRepository->findOneBy(['libelle' => 'Clôturée'])));
                } else {
                    $sortie->setEtat(($this->etatRepository->findOneBy(['libelle' => 'Ouverte'])));
                }

                if ($dateHeureDebut <= $dateActuelle) {
                    $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'Activité en cours']));
                    $this->entityManager->persist($sortie);
                    $this->entityManager->flush();
                }

                if ($dateHeureDebut->modify('+ ' . $sortie->getDuree() . 'minutes') <= $dateActuelle) {
                    $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'Passée']));

                }
                $dateHeureDebut->modify('- ' . $sortie->getDuree() . 'minutes');

                $this->entityManager->persist($sortie);
                $this->entityManager->flush();
            }
        }

    }
}