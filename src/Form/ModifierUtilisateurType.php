<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use App\Repository\CampusRepository;
use Faker\Provider\PhoneNumber;
use Faker\Provider\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ModifierUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo : ',
                //'attr' => ['class' => 'form-control']
            ])
//            ->add('roles')
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit être identique!',
                'label' => 'Mot de passe : ',
                'first_options'  => ['label' => 'Mot de passe :'],
                'second_options' => ['label' => 'Confirmez :'],
                'mapped' => false
            ])
            ->add('nom', TextType::class, [
                'label' => ' Nom : '
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom : '
            ])
            ->add('telephone', TelType::class, [
                'label' => 'N° de Téléphone : '
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email : '
            ])

            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus associé :',
                'query_builder' => function(CampusRepository $campusRepository){
                $qb = $campusRepository->createQueryBuilder("c");
                $qb->addOrderBy("c.nom");
                return $qb;
                }
            ])
            ->add('avatar', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                            "maxSize" => '5000k',
                            "mimeTypesMessage" => "Type de fichier non supporté! !",

                        ]
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
