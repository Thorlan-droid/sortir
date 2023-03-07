<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('/list', name: 'list', methods: 'GET')]
    public function list(): Response
    {
        return $this->render('sortie/list.html.twig');
    }

    #[Route('/{id}', name: 'show', requirements: ['id'=>'\d+'])]
    public function show(int $id, SortieRepository $sortieRepository) : Response {
        $sortie = $sortieRepository->find($id);

        if(!$sortie) {
            throw $this->createNotFoundException("Oops, page introuvable !");
        }
        return $this->render('/sortie/show.html.twig', [
            'sortie' => $sortie
        ]);
    }
    #[Route('/add', name: 'add')]
    public function add(
        SortieRepository $sortieRepository,
        Request $request
    ):Response
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

             if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                $sortieRepository->save($sortie, true);
                $this->addFlash("success", "Sortie ajoutée !");


             }
        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }
}

