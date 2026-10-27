<?php

namespace Evp\Component\Money;

use Maba\Component\Math\MathInterface as MabaMathInterface;
use Maba\Component\Monetary\Exception\InvalidAmountException;
use Maba\Component\Monetary\Exception\InvalidCurrencyException;
use Maba\Component\Monetary\Factory\MoneyFactoryInterface;
use Maba\Component\Monetary\Validation\MoneyValidator;

class MoneyFactory implements MoneyFactoryInterface
{
    protected $math;
    protected $validator;

    public function __construct(MabaMathInterface $math, MoneyValidator $validator)
    {
        $this->math = $math;
        $this->validator = $validator;
    }

    /**
     * @param string $amount
     * @param string $currency
     *
     * @return Money
     *
     * @throws InvalidAmountException
     * @throws InvalidCurrencyException
     */
    public function create($amount, $currency)
    {
        if ($amount !== null && !is_scalar($amount)) {
            throw new InvalidAmountException('Amount must be a number, ' . gettype($amount) . ' given');
        }
        try {
            $result = new Money($amount);
        } catch (MoneyException $exception) {
            throw new InvalidAmountException($exception->getMessage(), 0, $exception);
        }
        if ($currency !== null) {
            try {
                $result->setCurrency($currency);
            } catch (MoneyException $exception) {
                throw new InvalidCurrencyException($exception->getMessage(), 0, $exception);
            }
        }
        $this->validator->validateMoney($result);
        return $result;
    }

    /**
     * Create zero Money
     *
     * @param string $currency
     *
     * @return Money
     *
     * @throws InvalidAmountException
     * @throws InvalidCurrencyException
     */
    public function createZero($currency = null)
    {
        return $this->create('0', $currency);
    }

    /**
     * @param int $amountInCents
     * @param string $currency
     *
     * @return Money
     *
     * @throws InvalidAmountException
     * @throws InvalidCurrencyException
     */
    public function createFromCents($amountInCents, $currency)
    {
        if (!is_scalar($amountInCents) || !preg_match(Money::INTEGER_PATTERN, (string)$amountInCents)) {
            throw new InvalidAmountException('Amount in cents must be integer');
        }

        return $this->create($this->math->div($amountInCents, '100'), $currency);
    }
}
