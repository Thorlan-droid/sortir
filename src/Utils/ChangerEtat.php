<?php

namespace App\Utils;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChangerEtat
{
    protected $sortieRepository;
    protected $etatRepository;
    protected $entityManager;

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

    public function verifierEtat(): void
    {
        $sorties = $this->sortieRepository->findAll();
        $now = new \DateTime();
        $now->modify('+ 1 hour');

        foreach ($sorties as $value) {
            if ($value->getEtat() != 'Annulée' and $value->getEtat() != 'Créée') {

                $dateHeureDebut = $value->getDateHeureDebut();
                if ($value->getNbInscriptionsMax() == $value->getParticipants()->count() or $value->getDateLimiteInscription() < $now) {
                    $value->setEtat(($this->etatRepository->findOneBy(['libelle' => 'Clôturée'])));
                } else {
                    $value->setEtat(($this->etatRepository->findOneBy(['libelle' => 'Ouverte'])));
                }

                if ($dateHeureDebut <= $now) {
                    $value->setEtat($this->etatRepository->findOneBy(['libelle' => 'Activité en cours']));
                    $this->entityManager->persist($value);
                    $this->entityManager->flush();
                }

                if ($dateHeureDebut->modify('+ ' . $value->getDuree() . 'minutes') <= $now) {
                    $value->setEtat($this->etatRepository->findOneBy(['libelle' => 'Passée']));

                }
                $dateHeureDebut->modify('- ' . $value->getDuree() . 'minutes');

                $this->entityManager->persist($value);
                $this->entityManager->flush();
            }
        }

    }
}