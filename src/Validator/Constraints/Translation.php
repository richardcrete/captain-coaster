<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Translation extends Constraint
{
    public string $short = 'translation.short';
    public string $duplicate = 'translation.duplicate';
}
