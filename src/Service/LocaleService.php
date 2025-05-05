<?php

declare(strict_types=1);

namespace App\Service;

#[\AllowDynamicProperties] class LocaleService
{
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function getLocalesAsChoices(): array
    {
        $locales = [];

        foreach ($this->locales as $locale) {
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    public function getLocalesTotal(): int
    {
        return \count($this->locales);
    }
}
