<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['disabled' => true])
            ->add('prenom')
            ->add('nom')
            ->add('old_password', PasswordType::class, ['attr' => [
                'placeholder' => 'Veuillez saisir votre mot de passe actuel'
            ],
                'label' => 'Mot de passe actuel',
                'mapped' => false
            ])
            ->add('new_password', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Mot de passes non identiques',
                    'required' => true,
                    'first_options' =>  ['label' => 'Nouveau mot de passe'],
                    'second_options' => ['label' => 'Confirmation du nouveau mot de passe'],
                    'constraints' => new Length(['min' => 2, 'max' => 50]),
                    'mapped' => false
                ])
            ->add('submit', SubmitType::class, ['label' => "Mettre à jour", 'attr' => ['class' => 'btn-info mx-auto mt-5 px-3 py-2 d-block']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
