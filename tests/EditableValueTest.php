<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests;

use jonasarts\Bundle\RegistryBundle\Controller\EditableValue;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use PHPUnit\Framework\TestCase;

class EditableValueTest extends TestCase
{
    /**
     * Exposes the private trait method for testing.
     */
    private function sut(): object
    {
        return new class {
            use EditableValue;

            public function call(RegistryKeyType $type, mixed $value): string
            {
                return $this->valueToString($type, $value);
            }
        };
    }

    public function testNullBecomesEmptyString(): void
    {
        $this->assertSame('', $this->sut()->call(RegistryKeyType::STRING, null));
    }

    public function testStringIsReturnedAsIs(): void
    {
        $this->assertSame('hello', $this->sut()->call(RegistryKeyType::STRING, 'hello'));
    }

    public function testIntegerIsStringified(): void
    {
        $this->assertSame('42', $this->sut()->call(RegistryKeyType::INTEGER, 42));
    }

    public function testFloatIsStringified(): void
    {
        $this->assertSame('0.5', $this->sut()->call(RegistryKeyType::FLOAT, 0.5));
    }

    public function testBooleanIsStringified(): void
    {
        $this->assertSame('1', $this->sut()->call(RegistryKeyType::BOOLEAN, true));
        $this->assertSame('', $this->sut()->call(RegistryKeyType::BOOLEAN, false));
    }

    public function testDateTimestampIsFormattedAsDate(): void
    {
        $ts = strtotime('2013-10-16');

        $this->assertSame(date('Y-m-d', $ts), $this->sut()->call(RegistryKeyType::DATE, $ts));
    }

    public function testTimeTimestampIsFormattedAsTime(): void
    {
        $ts = strtotime('2013-10-16 13:45:30');

        $result = $this->sut()->call(RegistryKeyType::TIME, $ts);

        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $result);
        $this->assertSame(date('H:i:s', $ts), $result);
    }

    public function testAlreadyStringDateIsLeftUntouched(): void
    {
        // a non-int DATE value falls through to the string branch
        $this->assertSame('2013-10-16', $this->sut()->call(RegistryKeyType::DATE, '2013-10-16'));
    }

    public function testArrayIsJsonEncoded(): void
    {
        $data = ['a' => 'b', 'c' => 1];

        $this->assertSame(json_encode($data, \JSON_THROW_ON_ERROR), $this->sut()->call(RegistryKeyType::ARRAY, $data));
    }
}
