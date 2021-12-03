<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Carrier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('address', EntityType::class, [
                'label' => false,
                'class' => Address::class,
                'multiple' => false,
                'required' => true,
                'choices' => $user->getAddresses()
            ])
            ->add('carriers', EntityType::class, [
                'label' => 'Select you carrier',
                'class' => Carrier::class,
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'attr' => ['class' => 'custom_checkbox'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Summary',
                'attr' => [
                    'class' => 'btn btn-primary w-100'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => array()
        ]);
    }
}
