<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Defines the form structure for creating and editing Client (User) entities.
 * Implements context-aware logic to adapt field constraints (e.g., create vs edit modes).
 */
class ClientType extends AbstractType
{
    /**
     * Builds the form fields, dynamically adjusting requirements based on the context.
     * @param FormBuilderInterface $builder The form builder used to construct the form
     * @param array<string, mixed> $options Custom options passed to the form instance
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('email', EmailType::class, [
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer votre email',
                    ),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Client' => 'ROLE_CLIENT',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => !$isEdit
            ])
            ->add('nomClient', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('telClient', TextType::class, [
                'label' => 'Téléphone',
                'required' => !$isEdit
            ])
            ->add('adrClient', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $client = $event->getData();

            if (!$client->getCodeClient()) {
                $client->setCodeClient(strtolower((string) new Ulid()));
            }
        });
    }

    /**
     * Configures the default options for this form type, introducing custom options.
     * @param OptionsResolver $resolver The resolver for the form options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
            'is_edit' => false,
        ]);
    }
}
