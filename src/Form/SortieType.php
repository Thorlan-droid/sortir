<?php

namespace App\Form;

use App\Controller\LieuController;
use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use ContainerPuhunFN\getVilleRepositoryService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie :'
            ])
            ->add('campus', EntityType::class, [
                'label'=> 'campus : ',
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])

            ->add('ville', EntityType::class,[
                'label' => 'Ville :',
                'class'=> Ville::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une ville',
                'mapped' => false,
                'query_builder' => function(VilleRepository $villeRepository) {
                    return $villeRepository
                        ->createQueryBuilder("v")->addOrderBy("v.nom");
                }
            ])

            ->add('lieu', EntityType::class,[
                'label' => 'Lieu :',
                'class'=> Lieu::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un lieu',
                'mapped' => false,
                'query_builder' => function(LieuRepository $lieuRepository ) {
                    return $lieuRepository
                        ->createQueryBuilder("l")
                        ->addOrderBy('l.nom');
                }
            ])

            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure :',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('duree', TimeType::class)
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d inscription :',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('nbInscriptionMax', TextType::class, [
                'label' => 'Nombre de places :'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description :'
            ]);



            /*->add('campus', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'campus',
                'label' => 'Campus organisateur',
                /*  'query_builder' => function(SortieRepository $sortieRepository) {
                  $qb = $sortieRepository->createQueryBuilder("s");
                  $qb->addOrderBy("s.nom");
                  return $qb;
                  }
            ])*/

           /* ->add('codePostal', EntityType::class, [
                'class' => Ville::class,
                'label' => 'Code Postal',
                'choice_label' => function ($codePostal) {
                    return $codePostal->getCodePostal();
                }
            ])
        ->add('rue', EntityType::class, [
            'class' => Lieu::class,
            'label' => 'Rue',
            'choice_label' => function ($rue) {
                return $rue->getRue();
            }
        ]);*/

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
