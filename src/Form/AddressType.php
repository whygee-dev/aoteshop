<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [])
            ->add('nom',TextType::class, [])
            ->add('prenom',TextType::class, [])
            ->add('telephone',TelType::class, [])
            ->add('adresse',TextType::class, [])
            ->add('code_postal',TextType::class, [])
            ->add('ville',TextType::class, [])
            ->add('pays',CountryType::class, [])
            ->add('societe',TextType::class, ['attr' => ['placeholder' => '(facultatif)'], 'required' => false])
            ->add('submit',SubmitType::class, ['label' => 'Valider', 'attr' => ['class' => 'btn-block btn-info mx-auto mt-5 px-3 py-2 d-block']])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
