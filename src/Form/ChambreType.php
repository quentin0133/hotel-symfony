<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Enum\ChambreTypeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form structure for creating and editing room (Chambre) entities.
 */
class ChambreType extends AbstractType
{
    /**
     * Builds the form fields and maps them to the Chambre entity properties.
     * @param FormBuilderInterface $builder The form builder used to construct the form
     * @param array<string, mixed> $options Custom options passed to the form instance
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codeChambre', TextType::class)
            ->add('etage', IntegerType::class)
            ->add('nombreLit', IntegerType::class)
            ->add('type', EnumType::class, [
                'class' => ChambreTypeEnum::class,
                'placeholder' => '--- Choisir une type de chambre ---',
                'choice_label' => 'value',
                'required' => false
            ])
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'placeholder' => '--- Choisir un hôtel ---',
                'choice_label' => 'nomHotel'
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
            'data_class' => Chambre::class,
        ]);
    }
}
