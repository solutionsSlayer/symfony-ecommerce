<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrderCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $url;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $url)
    {
        $this->entityManager = $entityManager;
        $this->url = $url;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('detail', Action::new('updatePreparation', 'Preparation processing', "fas fa-truck")->linkToCrudAction('updatePreparation'))
            ->add('index', 'detail');
    }

    public function updatePreparation(AdminContext $context): RedirectResponse
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(2);
        $this->entityManager->flush();

        $this->addFlash("notice", "<span style='color:red'>The order N°<strong><u>". $order->getReference() ." as been successfully updated.</u></strong></span>");

        $url = $this->url
            ->setController(OrderCrudController::class)
            ->setAction(ACTION::INDEX)->generateUrl();

        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('user.firstname', 'User'),
            TextField::new('carrierName'),
            TextEditorField::new('deliuery', 'Delivery address'),
            MoneyField::new('carrierPrice')->setCurrency('EUR'),
            DateTimeField::new('createdAt'),
            MoneyField::new('total')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                0 => 'not paid',
                1 => 'paid',
                2 => 'preparation in process',
                3 => 'delivery in process'
            ])
        ];
    }

}
