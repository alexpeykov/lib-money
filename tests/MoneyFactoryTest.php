<?php

declare(strict_types=1);

namespace Evp\Component\Money\Tests;

use Evp\Component\Money\Money;
use Evp\Component\Money\MoneyFactory;
use Maba\Component\Math\BcMath;
use Maba\Component\Math\Math;
use Maba\Component\Math\NumberValidator;
use Maba\Component\Monetary\Exception\InvalidAmountException;
use Maba\Component\Monetary\Exception\InvalidCurrencyException;
use Maba\Component\Monetary\Factory\MoneyFactoryInterface;
use Maba\Component\Monetary\Information\MoneyInformationProvider;
use Maba\Component\Monetary\Validation\MoneyValidator;
use PHPUnit\Framework\TestCase;

class MoneyFactoryTest extends TestCase
{
    public function testImplementsTheMonetaryFactoryInterface()
    {
        $this->assertInstanceOf(MoneyFactoryInterface::class, $this->createFactory());
    }

    /**
     * @param array<int, string|int> $arguments
     *
     * @dataProvider createProvider
     */
    public function testCreate(string $method, array $arguments, Money $expected)
    {
        $result = $this->createFactory()->$method(...$arguments);

        $this->assertEquals($expected, $result);
        $this->assertSame($expected->getCurrency(), $result->getCurrency());
    }

    /**
     * @return array[]
     */
    public function createProvider()
    {
        return [
            'amount' => ['create', ['10.50', 'EUR'], new Money('10.50', 'EUR')],
            'zero' => ['createZero', ['EUR'], new Money('0', 'EUR')],
            'cents' => ['createFromCents', [1050, 'EUR'], new Money('10.500000', 'EUR')],
            'negative cents as a string' => ['createFromCents', ['-5', 'EUR'], new Money('-0.050000', 'EUR')],
        ];
    }

    /**
     * @dataProvider invalidCentsProvider
     */
    public function testCreateFromCentsRejectsANonInteger(string $amountInCents)
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount in cents must be integer');

        $this->createFactory()->createFromCents($amountInCents, 'EUR');
    }

    /**
     * @return array[]
     */
    public function invalidCentsProvider()
    {
        return [
            'a trailing line feed' => ["1234\n"],
            'a decimal value' => ['12.5'],
        ];
    }

    public function testCreateValidatesTheCurrencyAgainstTheInformationProvider()
    {
        $this->expectException(InvalidCurrencyException::class);

        $this->createFactory(['USD'])->create('1', 'EUR');
    }

    /**
     * @param string[]|null $availableCurrencies
     *
     * @return MoneyFactory
     */
    private function createFactory($availableCurrencies = null)
    {
        $numberValidator = new NumberValidator();
        $math = new Math(new BcMath(6, $numberValidator));

        return new MoneyFactory(
            $math,
            new MoneyValidator($math, new MoneyInformationProvider(null, $availableCurrencies), $numberValidator)
        );
    }
}
