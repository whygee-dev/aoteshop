<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,
                ['required' => true,
                    'constraints' => new Length(['min' => 2, 'max' => 50])
                ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'constraints' => new Length(['min' => 2, 'max' => 50])
            ])
            ->add('email', EmailType::class,
                ['required' => true, 'constraints' => new Length(['min' => 2, 'max' => 50])])
            ->add('password', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Mot de passes non identiques',
                    'required' => true,
                    'first_options' =>  ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmation de mot de passe'],
                    'constraints' => new Length(['min' => 2, 'max' => 50])
                ])
            ->add('submit', SubmitType::class, ['label' => "S'inscrire", 'attr' => ['class' => 'btn-info mx-auto mt-5 px-3 py-2 d-block']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
