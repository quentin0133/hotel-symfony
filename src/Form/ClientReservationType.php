<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Entity\Reservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 */
class ClientReservationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateType::class)
            ->add('dateFin', DateType::class)
            ->add('commentaire', TextType::class, [
                'required' => false
            ])
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'placeholder' => '--- Choisir un hotel ---',
                'choice_label' => 'nomHotel'
            ])
            ->add('chambres', EntityType::class, [
                'class' => Chambre::class,
                'choice_label' => 'codeChambre',
                'multiple' => true,
                'expanded' => true
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
