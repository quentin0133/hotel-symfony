<?php
namespace App\Twig\Components;

use App\Entity\Reservation;
use App\Form\AdminReservationType;
use App\Form\ClientReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('admin_reservation_form')]
class AdminReservationForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?Reservation $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(AdminReservationType::class, $this->initialFormData);
    }
}
