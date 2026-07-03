<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Hotel;
use App\Entity\Reservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form structure for creating and editing reservations in the administration panel.
 */
class AdminReservationType extends AbstractType
{
    /**
     * Builds the form fields and maps them to the Reservation entity properties.
     * @param FormBuilderInterface $builder The form builder used to construct the form
     * @param array<string, mixed> $options Custom options passed to the form instance
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numReservation', TextType::class)
            ->add('dateDebut', DateType::class)
            ->add('dateFin', DateType::class)
            ->add('commentaire', TextType::class, [
                'required' => false
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nomClient',
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
                'expanded' => true,
                'by_reference' => false
            ])
        ;
    }

    /**
     * Configures the default options for this form type.
     * @param OptionsResolver $resolver The resolver for the form options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
