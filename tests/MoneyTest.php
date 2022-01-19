<?php

namespace PatrickSamson\LaravelMoneybags\Tests;

use PatrickSamson\LaravelMoneybags\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    private \Faker\Generator $faker;

    public function setUp(): void
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * @test
     */
    public function testDefaultsToZero()
    {
        $money = new Money();

        $this->assertTrue($money->isZero());
        $this->assertEquals(0, $money->inCents());
    }

    /**
     * @test
     */
    public function testCreatesNewZeroValue()
    {
        $money = Money::zero();

        $this->assertTrue($money->isZero());
        $this->assertEquals(0, $money->inCents());
    }

    /**
     * @test
     * @dataProvider providesCreationFromConstructorScenarios
     */
    public function testCreatesNewFromConstructor(int $expected, $amount)
    {
        $money = new Money($amount);

        $this->assertEquals($expected, $money->inCents());
    }

    public function providesCreationFromConstructorScenarios()
    {
        return [
            // Integers
            [-12345, -12345],
            [0, 0],
            [12345, 12345],

            // Floats (truncated to `int`)
            [-12345, -12345.67],
            [0, 0.00],
            [12345, 12345.67],

            // Strings
            [-12345, '-12345'],
            [0, '0'],
            [0, '-0'],
            [12345, '12345'],
            [-12345, '-12345.67'],
            [0, '0.00'],
            [0, '-0.00'],
            [12345, '12345.67'],
        ];
    }

    /**
     * @test
     * @dataProvider providesCreationFromCentsScenarios
     */
    public function testCreatesNewFromCents(int $expected, $amount)
    {
        $money = Money::fromCents($amount);

        $this->assertEquals($expected, $money->inCents());
    }

    public function providesCreationFromCentsScenarios()
    {
        return [
            // Integers
            [-12345, -12345],
            [0, 0],
            [12345, 12345],

            // Floats (truncated to `int`)
            [-12345, -12345.67],
            [0, 0.00],
            [12345, 12345.67],

            // Strings
            [-12345, '-12345'],
            [0, '0'],
            [0, '-0'],
            [12345, '12345'],
            [-12345, '-12345.67'],
            [0, '0.00'],
            [0, '-0.00'],
            [12345, '12345.67'],
        ];
    }

    /**
     * @test
     * @dataProvider providesCreationFromDollarsScenarios
     */
    public function testCreatesNewFromDollars(int $expected, $amount)
    {
        $money = Money::fromDollars($amount);

        $this->assertEquals($expected, $money->inCents());
    }

    public function providesCreationFromDollarsScenarios()
    {
        return [
            // Integers
            [-1234500, -12345],
            [0, 0],
            [1234500, 12345],

            // Floats (truncated to `int`)
            [-1234567, -12345.67],
            [0, 0.00],
            [1234567, 12345.67],

            // Strings
            [-1234500, '-12345'],
            [0, '0'],
            [0, '-0'],
            [1234500, '12345'],
            [-1234567, '-12345.67'],
            [0, '0.00'],
            [0, '-0.00'],
            [1234567, '12345.67'],
        ];
    }

    /**
     * @test
     * @dataProvider providesAbsoluteScenarios
     */
    public function testAbsolute(int $expected, $amount)
    {
        $money = new Money($amount);

        $this->assertEquals($expected, $money->absolute()->inCents());
    }

    public function providesAbsoluteScenarios()
    {
        return [
            // Integers
            [12345, -12345],
            [0, 0],
            [12345, 12345],

            // Strings
            [12345, '-12345'],
            [0, '0'],
            [0, '-0'],
            [12345, '12345'],
        ];
    }

    /**
     * @test
     * @dataProvider providesInvertSignScenarios
     */
    public function testInvertSign(int $expected, $amount)
    {
        $money = new Money($amount);

        $this->assertEquals($expected, $money->invertSign()->inCents());
    }

    public function providesInvertSignScenarios()
    {
        return [
            // Integers
            [12345, -12345],
            [0, 0],
            [-12345, 12345],

            // Strings
            [12345, '-12345'],
            [0, '0'],
            [0, '-0'],
            [-12345, '12345'],
        ];
    }

    /**
     * @test
     * @dataProvider providesComputedAttributeScenarios
     *
     * @param mixed $expected
     */
    public function testHasComputedAttributes(int|string $amount, string $attribute, $expected)
    {
        $money = new Money($amount);
        $this->assertEquals($expected, $money->$attribute());
    }

    public function providesComputedAttributeScenarios()
    {
        return [
            '0 is zero' => [0, 'isZero', true],
            '10 is not zero' => [10, 'isZero', false],
            '-10 is not zero' => [-10, 'isZero', false],
            '"0" is zero' => ['0', 'isZero', true],
            '"0.00" is zero' => ['0.00', 'isZero', true],
            '"-0" is zero' => ['-0', 'isZero', true],
            '"-0.00" is zero' => ['-0.00', 'isZero', true],

            '-1 is non-zero' => [-1, 'isNonZero', true],
            '19 is non-zero' => [19, 'isNonZero', true],
            '0 is not non-zero' => [0, 'isNonZero', false],
            '"0" is not non-zero' => ['0', 'isNonZero', false],
            '"0.00" is not non-zero' => ['0.00', 'isNonZero', false],
            '"-0" is not non-zero' => ['-0', 'isNonZero', false],
            '"-0.00" is not non-zero' => ['-0.00', 'isNonZero', false],

            '42 is positive' => [42, 'isPositive', true],
            '-11 is not positive' => [-11, 'isPositive', false],
            '0 is positive' => [0, 'isPositive', true],

            '-12 is negative' => [-12, 'isNegative', true],
            '51 is not negative' => [51, 'isNegative', false],
            '0 is not negative' => [0, 'isNegative', false],
        ];
    }

    /**
     * @test
     * @dataProvider providesEqualityComparisonScenarios
     *
     * @param mixed $expected
     */
    public function testEqualityComparison(int|string $amount, int|string $operandAmount, bool $expectEquals)
    {
        $money = new Money($amount);
        $operand = new Money($operandAmount);

        $this->assertEquals($expectEquals, $money->isEqualTo($operand));
        $this->assertEquals(! $expectEquals, $money->isNotEqualTo($operand));

        if ($expectEquals) {
            // TODO greaterThan / lessThan
        }
    }

    public function providesEqualityComparisonScenarios()
    {
        return [
            [0, 0, true],
            [0, '0', true],
            [1234, 1234, true],
            [1234, '1234', true],
            [-1234, -1234, true],
            [-1234, '-1234', true],

            [1234, -1234, false],
            [1234, '-1234', false],
        ];
    }

    /**
     * @test
     */
    public function testClone()
    {
        $original = new Money(123);
        $clone = $original->clone();

        $this->assertEquals(123, $original->inCents());
        $this->assertEquals(123, $clone->inCents());
    }

    /**
     * @test
     * @dataProvider providesOperationsImmutablyScenarios
     */
    public function testOperatesImmutably(string $operation, callable $expected)
    {
        $amount1 = $this->faker->numberBetween(1, 1000000);
        $amount2 = $this->faker->numberBetween(1, 1000000);

        $expected = $expected($amount1, $amount2);

        $money1 = new Money($amount1);
        $money2 = new Money($amount2);

        $result = $money1->$operation($money2);

        $this->assertEquals($expected, $result->inCents());

        // The original money objects remain unchanged
        $this->assertEquals($amount1, $money1->amount());
        $this->assertEquals($amount2, $money2->amount());
    }

    public function providesOperationsImmutablyScenarios()
    {
        yield 'Addition' => [
            'add',
            fn ($a, $b) => $a + $b,
        ];

        yield 'Subtraction' => [
            'subtract',
            fn ($a, $b) => $a - $b,
        ];

        yield 'Multiplication' => [
            'multiplyByMoney',
            fn ($a, $b) => $a * $b,
        ];

        yield 'Division' => [
            'divideByMoney',
            fn ($a, $b) => (int) floor($a / $b),
        ];
    }
}
