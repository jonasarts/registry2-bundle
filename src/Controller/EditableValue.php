<?php

declare(strict_types=1);

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Controller;

use DateTimeInterface;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use JsonException;

/**
 * Shared rendering of a stored value into an editable form string,
 * used by the CRUD controllers.
 */
trait EditableValue
{
    /**
     * Convert a (typed) stored value into the string shown in the edit form.
     *
     * Date/time values are decoded to unix timestamps on read; render them as a
     * human-readable string so the form round-trips cleanly.
     *
     * @throws JsonException
     */
    private function valueToString(RegistryKeyType $type, mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if (RegistryKeyType::DATE === $type && is_int($value)) {
            return date('Y-m-d', $value);
        }

        if (RegistryKeyType::TIME === $type && is_int($value)) {
            return date('H:i:s', $value);
        }

        return match (true) {
            is_string($value) => $value,
            is_scalar($value) => (string) $value,
            $value instanceof DateTimeInterface => $value->format(DateTimeInterface::ATOM),
            default => json_encode($value, \JSON_THROW_ON_ERROR),
        };
    }
}
