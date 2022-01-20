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

            // Precision (truncated to `int`)
            [12, 12.345678901234567890],
            [12, '12.345678901234567890'],
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

            // Precision (truncated to `int`)
            [12, 12.345678901234567890],
            [12, '12.345678901234567890'],
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

            // Precision (truncated to `int`)
            [1234, 12.345678901234567890],
            [1234, '12.345678901234567890'],
            [PHP_INT_MAX, PHP_INT_MAX],
            //[1234, PHP_FLOAT_MAX], // TODO inCents() casts to int...
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
    public function testHasComputedAttributes(int|float|string $amount, string $attribute, $expected)
    {
        $money = new Money($amount);
        $this->assertEquals($expected, $money->$attribute());
    }

    public function providesComputedAttributeScenarios()
    {
        return [
            '0 is zero' => [0, 'isZero', true],
            '1 is not zero' => [1, 'isZero', false],
            '-1 is not zero' => [-1, 'isZero', false],
            '0.00000 is zero' => [0.00000, 'isZero', true],
            '0.00001 is not zero' => [0.00001, 'isZero', false],
            '-0.0001 is not zero' => [-0.00001, 'isZero', false],
            '"0" is zero' => ['0', 'isZero', true],
            '"0.00" is zero' => ['0.00', 'isZero', true],
            '"-0" is zero' => ['-0', 'isZero', true],
            '"-0.00" is zero' => ['-0.00', 'isZero', true],
            'Max float is not zero' => [PHP_FLOAT_MAX, 'isZero', false],
            // Technically not zero, but smaller than our scale : 2.2250738585072014E-308
            'Min float is not zero' => [PHP_FLOAT_MIN, 'isZero', true],

            '-1 is non-zero' => [-1, 'isNonZero', true],
            '1 is non-zero' => [1, 'isNonZero', true],
            '0 is not non-zero' => [0, 'isNonZero', false],
            '0.00000 is not non-zero' => [0.00000, 'isNonZero', false],
            '0.00001 is non-zero' => [0.00001, 'isNonZero', true],
            '-0.0001 is non-zero' => [-0.00001, 'isNonZero', true],
            '"0" is not non-zero' => ['0', 'isNonZero', false],
            '"0.00" is not non-zero' => ['0.00', 'isNonZero', false],
            '"-0" is not non-zero' => ['-0', 'isNonZero', false],
            '"-0.00" is not non-zero' => ['-0.00', 'isNonZero', false],
            'Max float is not non-zero' => [PHP_FLOAT_MAX, 'isNonZero', true],
            // Technically not zero, but smaller than our scale : 2.2250738585072014E-308
            'Min float is not non-zero' => [PHP_FLOAT_MIN, 'isNonZero', false],

            '1 is positive' => [1, 'isPositive', true],
            '-1 is not positive' => [-1, 'isPositive', false],
            '0 is positive' => [0, 'isPositive', true],
            'Max int is positive' => [PHP_INT_MAX, 'isPositive', true],
            'Min int is not positive' => [PHP_INT_MIN, 'isPositive', false],
            'Max float is positive' => [PHP_FLOAT_MAX, 'isPositive', true],
            'Min float is not positive' => [-PHP_FLOAT_MAX, 'isPositive', false],

            '-1 is negative' => [-1, 'isNegative', true],
            '1 is not negative' => [1, 'isNegative', false],
            '0 is not negative' => [0, 'isNegative', false],
            'Max int is not negative' => [PHP_INT_MAX, 'isNegative', false],
            'Min int is negative' => [PHP_INT_MIN, 'isNegative', true],
            'Max float is not negative' => [PHP_FLOAT_MAX, 'isNegative', false],
            'Min float is negative' => [-PHP_FLOAT_MAX, 'isNegative', true],
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

        $original->multiplyBy(2);
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
            fn ($a, $b) => (int) round($a / $b),
        ];
    }

    public function testMultiplyByPrecision()
    {
        $money = new Money(100);
        $this->assertEquals(115, $money->multiplyBy('1.14975')->inCents());
        $this->assertEquals(114, $money->multiplyBy('1.14975', round:false)->inCents());
    }
}
