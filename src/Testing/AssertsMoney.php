<?php

namespace PatrickSamson\LaravelMoneybags\Testing;

use Mockery\Matcher\Closure as MockeryClosure;
use PatrickSamson\LaravelMoneybags\Money;
use PHPUnit\Framework\TestCase as PHPUnit;

trait AssertsMoney
{
    /**
     * Asserts that two money objects are equal.
     */
    public function assertMoneyEqualsMoney(Money $expected, Money $actual, string $message = ''): void
    {
        PHPUnit::assertTrue(
            $expected->isEqualTo($actual),
            (
                ($message)
                    ? $message . PHP_EOL
                    : ''
            ) . sprintf('Expected Money amount %d does not match actual %d', $expected->amount(), $actual->amount())
        );
    }

    /**
     * Asserts that a money object is zero
     */
    public function assertMoneyIsZero(Money $actual, string $message = ''): void
    {
        $this->assertMoneyEqualsMoney(Money::zero(), $actual, $message);
    }

    /**
     * Assert cent value matches the expected money object.
     */
    public function assertMoneyEqualsCents(Money $expected, int $cents, string $message = ''): void
    {
        $this->assertMoneyEqualsMoney($expected, Money::fromCents($cents), $message);
    }

    /**
     * Assert dollar value matches the expected money object.
     *
     * @param int|float|string $dollars (string preferred)
     */
    public function assertMoneyEqualsDollars(Money $expected, $dollars, string $message = ''): void
    {
        $this->assertMoneyEqualsMoney($expected, Money::fromDollars($dollars), $message);
    }

    /**
     * Assert money object equals the expected amount of dollars.
     *
     * @param int|float|string $expectedDollars (string preferred)
     */
    public function assertDollarsEqualsMoney($expectedDollars, Money $actual, string $message = ''): void
    {
        $this->assertMoneyEqualsMoney(Money::fromDollars($expectedDollars), $actual, $message);
    }

    /**
     * Assert money object equals the expected amount of cents.
     */
    public function assertCentsEqualsMoney(int $expectedCents, Money $actual, string $message = ''): void
    {
        $this->assertMoneyEqualsMoney(Money::fromCents($expectedCents), $actual, $message);
    }

    /**
     * Mockery matcher for matching money argument.
     */
    public function matchMoney(Money $money): MockeryClosure
    {
        return new MockeryClosure(function ($arg) use ($money) {
            if ($money->isEqualTo($arg)) {
                return true;
            }

            print_r(sprintf(
                'Failed to match Money amount %d to received argument amount %d' . PHP_EOL,
                $money->amount(),
                $arg->amount()
            ));

            return false;
        });
    }
}
