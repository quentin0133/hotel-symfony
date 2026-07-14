<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Ulid;

/**
 * Defines the form structure for managing Hotel entities.
 * Kept intentionally straightforward as it maps directly to basic string properties.
 */
class HotelType extends AbstractType
{
    /**
     * Builds the form fields mapped to the Hotel entity.
     * @param FormBuilderInterface $builder The form builder used to construct the form
     * @param array<string, mixed> $options Custom options passed to the form instance
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomHotel', TextType::class)
            ->add('adresseHotel', TextType::class)
            ->add('categorieHotel', TextType::class)
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $hotel = $event->getData();

            if (!$hotel->getCodeHotel()) {
                $hotel->setCodeHotel(strtolower((string) new Ulid()));
            }
        });
    }

    /**
     * Configures the default options for this form type.
     * @param OptionsResolver $resolver The resolver for the form options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }
}
