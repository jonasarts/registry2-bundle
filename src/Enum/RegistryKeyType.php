<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Enum;

enum RegistryKeyType: string
{
    case INTEGER = 'i';
    case BOOLEAN = 'b';
    case STRING = 's';
    case FLOAT = 'f';
    case DATE = 'd';
    case TIME = 't';
    case ARRAY = 'a';
}