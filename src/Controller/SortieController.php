<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Form\CancelSortieType;
use App\Form\FiltreType;
use App\Form\Recherche\ModelFiltre;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\UserRepository;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;

use App\Entity\Sortie;
use App\Repository\VilleRepository;
use App\Utils\ChangerEtat;
use ContainerAOlQoqJ\getCampusRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Curl\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'list')]
    #[Route('/list', name: 'list')]
    public function profile(SortieRepository $sortieRepository, EntityManagerInterface $entityManager, Request $request, ChangerEtat $changerEtat, EtatRepository $etatRepository): Response
    {


        $changerEtat->changeState($etatRepository, $sortieRepository);


        $filtres = new ModelFiltre();
        $filtreForm = $this->createForm(FiltreType::class, $filtres);
        $filtreForm->handleRequest($request);

        $sortieFiltre = $sortieRepository->findFiltered($filtres);

//        dd($sortieFiltre);
        //$sortie = $sortieRepository->findAll();
        return $this->render('sortie/list.html.twig', [
            'sortieFiltre' => $sortieFiltre, 'filtre' => $filtreForm->createView(),

        ]);
    }



//    public function list(SortieRepository $sortieRepository): Response
//    {
//        $sorties = $sortieRepository->findAll();
//        return $this->render('sortie/list.html.twig', [
//            'sorties' => $sorties
//        ]);
//    }




    #[Route('/recherche', name: 'recherche')]
    public function rechercheParFiltre(SortieRepository $sortieRepository): Response
    {
        $filtres = new ModelFiltre();
        $filtreForm = $this->createForm(FiltreType::class, $filtres);
        $request = null;
        $filtreForm->handleRequest($request);

        //$sortieRepository = null;
        $sortieFiltre = $sortieRepository->findFiltered($filtres);

        return $this->render('sortie/list.html.twig', [
            'sortieFiltre' => $sortieFiltre, 'filtre' => $filtreForm->createView()
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
        EtatRepository   $etatRepository,
        Request          $request,
    ): Response
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $campus = $this->getUser()->getCampus();
            $organisateur = $this->getUser();
            $etat = $etatRepository->findOneBy(array('libelle' => 'Créée'));

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
    public function update(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        /* if ($sortie->getOrganisateur()->getId() !== $this->getUser()->getUserIdentifier()) {
             $this->addFlash("warning", "Vous n'êtes pas autorisé à modifier cette sortie");
         } */

        if (!$sortie) {
            throw $this->createNotFoundException('Nous n\'avons pas trouvé votre sortie');
        }

        if ($sortieForm->isSubmitted())/*&& $sortie->getOrganisateur()->getId() !== $this->getUser()->getUserIdentifier())   && $sortieForm->isValid() */{
            dump($sortie);
            $campus = $this->getUser()->getCampus();
            $organisateur = $this->getUser();
            $etat = $etatRepository->findOneBy(array('libelle' => 'Créée'));

            $campus->getNom();
            $sortie->setEtat($etat);
            $sortie->setCampus($campus);

            $sortie->setOrganisateur($organisateur);

            $sortieRepository->save($sortie, true);
            $this->addFlash("success", "Sortie modifiée !");
            return $this->redirectToRoute("sortie_list");
        }

        return $this->render('sortie/update.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'sortie' => $sortie

        ]);

        // return $this->redirectToRoute("sortie_list");

    }

    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id' => '\d+'])]
    public function cancel(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Nous n\'avons pas trouvé votre sortie');
        }

        $cancelSortieForm = $this->createForm(CancelSortieType::class, $sortie);

        $cancelSortieForm->handleRequest($request);

        if ($cancelSortieForm->isSubmitted() /*&& $cancelSortieForm->isValid()*/) {

            $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
            // Récupération de l'entité de l'état "Annulé"

            $sortie->setEtat($etat);
            // Modification de l'état de la sortie à l'état "Annulé"

            $sortieRepository->save($sortie, true);
            // Sauvegarde de la sortie modifiée

            $this->addFlash("success", "Sortie annulée !");

            return $this->redirectToRoute("sortie_list");
        }

        return $this->render("/sortie/cancel.html.twig", [
            'sortie' => $sortie,
            'cancelSortieForm' => $cancelSortieForm->createView()
        ]);
    }



    #[Route('/publish/{id}', name: 'publish', requirements: ['id' => '\d+'])]
    public function publish(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Nous n\'avons pas trouvé votre sortie');
        }

            $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            // Récupération de l'entité de l'état "Ouverte"

            $sortie->setEtat($etat);
            // Modification de l'état de la sortie à l'état "Ouverte"

            $sortieRepository->save($sortie, true);
            // Sauvegarde de la sortie modifiée

            $this->addFlash("success", "Sortie publiée !");

            return $this->redirectToRoute("sortie_list");


        return $this->render('sortie/update.html.twig', [

            'sortie' => $sortie

        ]);
    }



    #[Route('/remove/{id}', name: 'remove')]
    public function remove(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
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

    #[Route('/subscribe/{id}', name: 'subscribe', requirements: ['id' => '\d+'])]
    public function subscribe(int $id, SortieRepository $sortieRepository)
    {

        $sortie = $sortieRepository->find($id);
        $sortieEtat = $sortie->getEtat()->getLibelle();
        $user = $this->getUser();
        $inscrits = $sortie->getInscrits()->count();
        $nbInscriptionMax = $sortie->getNbInscriptionMax();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();
        $dateActuelle = new \DateTime();

        if ((!$sortie->getInscrits()->contains($user)) && (!$user->getSorties()->contains($sortie))
            && ($sortieEtat == "Ouverte")
            && ($dateLimiteInscription >= $dateActuelle)
            && ($nbInscriptionMax > $inscrits)) {

            $sortie->addInscrit($user);
            $sortieRepository->save($sortie, true);
            $this->addFlash("success", 'Votre êtes inscrit à cette sortie !');
        }
        return $this->redirectToRoute("sortie_list");
    }

    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id' => '\d+'])]
    public function unsubscribe(int $id, SortieRepository $sortieRepository)
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();

        if ($sortie->getInscrits()->contains($user)) {
            $sortie->removeInscrit($user);
            $sortieRepository->save($sortie, true);
            $this->addFlash('warning', 'Vous êtes désinscrit de cette sortie');
        }
        return $this->redirectToRoute("sortie_list");
    }

}

