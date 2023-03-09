<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\UserRepository;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;

use App\Entity\Sortie;
use App\Utils\ChangerEtat;
use ContainerAOlQoqJ\getCampusRepositoryService;
use http\Client\Curl\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('', name: 'list')]
    #[Route('/list', name: 'list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties
        ]);
    }

    #[Route('/recherche', name: 'recherche')]
    public function rechercheParFiltre(
        CampusRepository $campusRepository,
        SortieRepository $sortieRepository,
        ChangerEtat      $changerEtat,

    ): Response

    {

        $choixCampus = filter_input(INPUT_POST, 'Campus-select', FILTER_SANITIZE_STRING);

        $choixRecherche = filter_input(INPUT_POST, 'rechercher', FILTER_SANITIZE_STRING);

        $choixDateDebut = filter_input(INPUT_POST, 'debut-sortie', FILTER_SANITIZE_STRING);

        $choixDateFin = filter_input(INPUT_POST, 'fin-sortie', FILTER_SANITIZE_STRING);

        $choixOrganisateur = filter_input(INPUT_POST, 'organisateur', FILTER_VALIDATE_INT);

        $choixInscrit = filter_input(INPUT_POST, 'inscrit', FILTER_VALIDATE_INT);

        $choixPasInscrit = filter_input(INPUT_POST, 'pasInscrit', FILTER_VALIDATE_INT);

        $choixPassee = filter_input(INPUT_POST, 'passee', FILTER_SANITIZE_STRING);


        if ($choixCampus != 'Tous') {
            $choixCampus = $campusRepository->findOneBy(['nom' => $choixCampus]);
            $choixCampus = $choixCampus->getId();
        } else {
            $choixCampus = -1;
        }
        if ((($choixDateDebut != null) and ($choixDateFin == null)) or (($choixDateFin != null) and $choixDateDebut == null)) {
            $this->addFlash('error', 'Veuillez sélectionner les deux dates');
            $sorties = $sortieRepository->findAll();

        } else {
            $sorties = $sortieRepository->selectSortiesAvecFiltres($choixCampus, $choixRecherche, $choixDateDebut, $choixDateFin,
                $choixOrganisateur, $choixInscrit, $choixPasInscrit, $choixPassee);
        }

        $changerEtat->verifierEtat();
        $campus = $campusRepository->findAll();

        return $this->render('sortie/list.html.twig', [
            "sortie" => $sorties,
            "campus" => $campus,
            "choixCampus" => $choixCampus,
            "choixRecherche" => $choixRecherche,
            "choixDateDebut" => $choixDateDebut,
            "choixDateFin" => $choixDateFin,
            "choixOrganisateur" => $choixOrganisateur,
            'choixInscrit' => $choixInscrit,
            'choixPasInscrit' => $choixPasInscrit,
            'choixPassee' => $choixPassee,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oops, page introuvable !");
        }
        return $this->render('/sortie/show.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        LieuController $lieuController,
        Request          $request,
    ): Response
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $campus = $this->getUser()->getCampus();
            $organisateur = $this->getUser();
            $etat = $etatRepository->findOneBy(array('libelle'=>'Creee'));

            $campus->getNom();
            $sortie->setEtat($etat);
            $sortie->setCampus($campus);

            $sortie->setOrganisateur($organisateur);

            $sortieRepository->save($sortie, true);
            $this->addFlash("success", "Sortie ajoutée !");
            return $this->redirectToRoute("sortie_list");

        }
        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function update(int $id, SortieRepository $sortieRepository) : Response
    {
        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            throw $this->createNotFoundException('Nous n\'avons pas trouvé votre sortie');
        }
        $sortieForm= $this->createForm(SortieType::class, $sortie);

        return $this->render('/sortie/update.html.twig', [
           'sortie'=>$sortie,
           'sortieUpdateForm' => $sortieForm->createView()
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(int $id, SortieRepository $sortieRepository)
    {
        $sortie = $sortieRepository->find($id);

        if ($sortie) {
            $sortieRepository->remove($sortie, true);
            $this->addFlash("warning", 'Votre sortie a bien été supprimée');
        } else {
            throw $this->createNotFoundException('Cette sortie n\'existe pas');
        }
        return $this->redirectToRoute('sortie_list');
    }

}

