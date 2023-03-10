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
    public function profile(SortieRepository $sortieRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        $date = new \DateTime();
        $date->sub(new \DateInterval('P1M')); // soustraire 1 mois

        $sorties = $sortieRepository->findOldSorties($date);

        foreach ($sorties as $sortie) {
            $etatHistorise = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Historisée']);
            if (!$etatHistorise) {
                $etatHistorise = new Etat();
                $etatHistorise->setLibelle('Historisée');
                $entityManager->persist($etatHistorise);
            }

            $sortie->setEtat($etatHistorise);
            $entityManager->flush();
        }


        $filtres = new ModelFiltre();
        $filtreForm = $this->createForm(FiltreType::class, $filtres);
        $filtreForm->handleRequest($request);

        $sortieFiltre = $sortieRepository->findFiltered($filtres);

//        dd($sortieFiltre);
        //$sortie = $sortieRepository->findAll();
        return $this->render('sortie/list.html.twig', [
            'sortieFiltre' => $sortieFiltre, 'filtre' => $filtreForm->createView(),
            'sorties' => $sorties,
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
        Request          $request,
        ChangerEtat      $changerEtat,
      //  Sortie $sortie,
    ): Response
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $campus = $this->getUser()->getCampus();
            $organisateur = $this->getUser();
            $etat = $changerEtat->changerSortie($sortie);
            $etat = $changerEtat->changerEtat();

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
    public function update(int $id, SortieRepository $sortieRepository, ChangerEtat $changerEtat, Request $request): Response
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

        if ($sortieForm->isSubmitted() && $sortieForm->isValid())/*&& $sortie->getOrganisateur()->getId() !== $this->getUser()->getUserIdentifier())*/ {

            $campus = $this->getUser()->getCampus();
            $organisateur = $this->getUser();
            $etat = $changerEtat->changerEtat();

            $campus->getNom();
            $sortie->setEtat($etat);
            $sortie->setCampus($campus);

            $sortie->setOrganisateur($organisateur);

            $sortieRepository->save($sortie, true);
            $this->addFlash("success", "Sortie modifiée !");
            return $this->redirectToRoute("sortie_list");
        }


        return $this->render('/sortie/update.html.twig', [
            'sortie' => $sortie,
            'sortieForm' => $sortieForm->createView()
        ]);

        // return $this->redirectToRoute("sortie_list");

    }

    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id' => '\d+'])]
    public function cancel(int                    $id,
                           SortieRepository       $sortieRepository,
                           ChangerEtat            $changerEtat,
                           Request                $request,
                           ): Response
    {
        $sortie = $sortieRepository->find($id);
        $cancelSortieForm = $this->createForm(CancelSortieType::class, $sortie);
        $cancelSortieForm->handleRequest($request);


        if (!$sortie) {
            throw $this->createNotFoundException('Nous n\'avons pas trouvé votre sortie');
        }

        if ($cancelSortieForm->isSubmitted() && $cancelSortieForm->isValid())/*&& $sortie->getOrganisateur()->getId() !== $this->getUser()->getUserIdentifier())*/ {

            $campus = $this->getUser()->getCampus();
            $organisateur = $this->getUser();
//          $etat = $etatRepository->findOneBy(array('libelle' => 'Annulée'));

            $etat = $changerEtat->changerEtat();

            $campus->getNom();
            $sortie->setEtat($etat);
            $sortie->setCampus($campus);

            $sortie->setOrganisateur($organisateur);

            $sortieRepository->save($sortie, true);

            $this->addFlash("success", "Sortie annulée !");

            return $this->redirectToRoute("sortie_list");
        }


        return $this->render('/sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'cancelSortieForm' => $cancelSortieForm->createView()
        ]);

        // return $this->redirectToRoute("sortie_list");

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

