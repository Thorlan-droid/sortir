<?php

namespace App\Form;

use App\Entity\Sortie;
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
            ->add('nom', TextType::class)
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date heure debut',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('duree', TimeType::class)
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite inscription',
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('infosSortie', TextareaType::class)
            ->add('inscrits')
            ->add('organisateur')
            ->add('campus')
            ->add('lieu')
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    "Creee" => "creee",
                    "Ouverte" => "ouverte",
                    "Cloturee" => "cloturee",
                    "Activite en cours" => "Activite en cours",
                    "Passee" => "Passee",
                    "Annulee" => "Annulee"
                ],
                'multiple' => false,
                'expanded' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
