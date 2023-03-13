<?php

namespace App\Controller;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EtatController extends AbstractController
{
    #[Route('/etat', name: 'app_etat')]
    public function etatSortie(EtatRepository $etatRepository, SortieRepository $sortieRepository): Response
    {
        return $this->render('etat/etat.html.twig'
            );
    }
}
