<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Service\LocaleService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[\AllowDynamicProperties]
class TranslationValidator extends ConstraintValidator
{
    private LocaleService $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!\is_array($value) && !$value instanceof \IteratorAggregate) {
            throw new UnexpectedTypeException($value, 'IteratorAggregate');
        }

        if (\count($value) !== $this->localeService->getLocalesTotal()) {
            $this->context->buildViolation($constraint->short)->setParameter('%num%', (string) $this->localeService->getLocalesTotal())->addViolation();

            return;
        }

        $definedTranslationLocales = [];
        foreach ($value as $element) {
            if (\in_array($element->getLocale(), $definedTranslationLocales)) {
                $this->context->buildViolation($constraint->duplicate)
                    ->setParameter('%locale%', strtoupper($element->getLocale()))
                    ->addViolation();

                return;
            }

            $definedTranslationLocales[] = $element->getLocale();
        }
    }
}
