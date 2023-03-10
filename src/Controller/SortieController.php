<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Form\FiltreType;
use App\Form\Recherche\ModelFiltre;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\UserRepository;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;

use App\Entity\Sortie;
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
            'sortieFiltre'=>$sortieFiltre, 'filtre' => $filtreForm->createView(),
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
    public function rechercheParFiltre(): Response {
        $filtres = new ModelFiltre();
        $filtreForm = $this->createForm(FiltreType::class, $filtres);
        $request = null;
        $filtreForm->handleRequest($request);

        $sortieRepository = null;
        $sortieFiltre = $sortieRepository->findFiltered($filtres);

        return $this->render('sortie/list.html.twig', [
            'sortieFiltre'=>$sortieFiltre, 'filtre' => $filtreForm->createView()
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
            $etat = $etatRepository->findOneBy(array('libelle' => 'Creee'));

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

        if (!$sortie) {
            throw $this->createNotFoundException('Nous n\'avons pas trouvé votre sortie');
        }
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $campus = $this->getUser()->getCampus();
            $organisateur = $this->getUser();
            $etat = $etatRepository->findOneBy(array('libelle' => 'Creee'));

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

    #[Route('/subscribe/{id}', name: 'subscribe', requirements: ['id' => '\d+'])]
    public function subscribe(int $id, SortieRepository $sortieRepository, UserRepository $userRepository)
    {
        $sortie = $sortieRepository->find($id);
        $sortieEtat = $sortie->getEtat();
        $user = $this->getUser();

        if ((!$sortie->getInscrits()->contains($user)) && (!$user->getSorties()->contains($sortie)) && ($sortieEtat!="Clôturée")) {
            $sortie->addInscrit($user);
            $user->addSorties($sortie);
            $userRepository->save($user, true);
            $sortieRepository->save($sortie, true);
            $this->addFlash("succes", 'Votre êtes inscrit à cette sortie !');
        }

        return $this->redirectToRoute("sortie_list");

    }

    #[Route('/unsubscribe', name: 'unsubscribe')]
    public function unsubscribe(int $id, SortieRepository $sortieRepository)
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser()->getUserIdentifier();

        if ($sortie->getInscrits()->contains($user)) {

            $sortie->removeInscrit($user);
            $sortieRepository->save($sortie, true);
            $this->addFlash('warning', 'Vous êtes désinscrit de cette sortie');
        }
        return $this->redirectToRoute("sortie_list");
    }

}

