<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Enum;

enum RegistryKeyType: string
{
    case BOOLEAN = 'b';
    case INTEGER = 'i';
    case FLOAT = 'f';
    case STRING = 's';
    case DATE = 'd';
    case TIME = 't';
    case ARRAY = 'a';
}
