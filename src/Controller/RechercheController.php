<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'app_recherche')]
    public function recherche(Request $request): Response
    {
        $query = $request->query->get('rechercher', 'debut-sortie','fin-sortie', 'organisateur', 'inscrit', 'pasInscrit', 'passee');

        $results = null;

        return $this->render('recherche/list.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);

    }



}
