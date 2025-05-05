<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ContinentTranslation;
use App\Service\LocaleService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContinentTranslationCrudController extends AbstractCrudController
{
    private LocaleService $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    public static function getEntityFqcn(): string
    {
        return ContinentTranslation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name')->setColumns('col-12'),
            ChoiceField::new('locale')
                ->setChoices($this->localeService->getLocalesAsChoices())
                ->setColumns('col-12'),
        ];
    }
}
