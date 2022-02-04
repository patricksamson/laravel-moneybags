<?php

namespace PatrickSamson\LaravelMoneybags\Tests;

use DivisionByZeroError;
use PatrickSamson\LaravelMoneybags\Money;
use PatrickSamson\LaravelMoneybags\Testing\AssertsMoney;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    use AssertsMoney;

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
     * @dataProvider providesAdditionScenarios
     */
    public function testAdd(int|float|string $amount, int|float|string $operand, int $expected)
    {
        $money = new Money($amount);
        $operandMoney = new Money($operand);

        $this->assertMoneyEqualsMoney(new Money($expected), $money->add($operandMoney));
    }

    public function providesAdditionScenarios()
    {
        return [
            [0, 0, 0],
            [1, -1, 0],
            [-1, -1, -2],
            [PHP_INT_MAX, PHP_INT_MIN, -1],
            [PHP_FLOAT_MAX, -PHP_FLOAT_MAX, 0],
            [PHP_FLOAT_MIN, PHP_FLOAT_MIN, 0],
            [PHP_FLOAT_MIN, -PHP_FLOAT_MIN, 0],
            [0, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 0],
            [0.00, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 0],
        ];
    }

    /**
     * @test
     * @dataProvider providesSubscractionScenarios
     */
    public function testSubtract(int|float|string $amount, int|float|string $operand, int $expected)
    {
        $money = new Money($amount);
        $operandMoney = new Money($operand);

        $this->assertMoneyEqualsMoney(new Money($expected), $money->subtract($operandMoney));
    }

    public function providesSubscractionScenarios()
    {
        return [
            [0, 0, 0],
            [1, 1, 0],
            [1, -1, 2],
            [-1, -1, 0],
            [PHP_INT_MAX, -PHP_INT_MIN, -1],
            [PHP_FLOAT_MAX, PHP_FLOAT_MAX, 0],
            [PHP_FLOAT_MIN, PHP_FLOAT_MIN, 0],
            [PHP_FLOAT_MIN, -PHP_FLOAT_MIN, 0],
            [0, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 0],
            [0.00, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 0],
        ];
    }

    /**
     * @test
     * @dataProvider providesMultiplicationScenarios
     */
    public function testMultiplication(int|float|string $amount, int|float|string $operand, int $expected)
    {
        $money = new Money($amount);
        $operandMoney = new Money($operand);

        $this->assertMoneyEqualsMoney(new Money($expected), $money->multiplyBy($operand));
        $this->assertMoneyEqualsMoney(new Money($expected), $money->multiplyByMoney($operandMoney));
        $this->assertMoneyEqualsMoney($money->multiplyBy($operand), $money->multiplyByMoney($operandMoney));
    }

    public function providesMultiplicationScenarios()
    {
        return [
            [0, 0, 0],
            [1, 0, 0],
            [1, 1, 1],
            [1, -1, -1],
            [-1, -1, 1],
            //[PHP_INT_MAX, -PHP_INT_MIN, -1], // TODO Too large for inCents()
            //[PHP_FLOAT_MAX, PHP_FLOAT_MAX, 0],
            [PHP_FLOAT_MIN, PHP_FLOAT_MIN, 0],
            [PHP_FLOAT_MIN, -PHP_FLOAT_MIN, 0],
            [1, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 0],
            [1.00, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 0],

            // Rounding tests
            [100, 1 / 3, 33],
            [100, 2 / 3, 67],
        ];
    }

    /**
     * @test
     * @dataProvider providesDivisionScenarios
     */
    public function testDivision(int|float|string $amount, int|float|string $operand, int $expected)
    {
        $money = new Money($amount);
        $operandMoney = new Money($operand);

        $this->assertMoneyEqualsMoney(new Money($expected), $money->divideBy($operand));
        $this->assertMoneyEqualsMoney(new Money($expected), $money->divideByMoney($operandMoney));
        $this->assertMoneyEqualsMoney($money->divideBy($operand), $money->divideByMoney($operandMoney));
    }

    public function providesDivisionScenarios()
    {
        return [
            [0, 1, 0],
            [1, 1, 1],
            [1, -1, -1],
            [-1, -1, 1],
            [PHP_INT_MAX, -PHP_INT_MIN, 1],
            [PHP_FLOAT_MAX, PHP_FLOAT_MAX, 1],
            [1, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 10 ** (Money::DEFAULT_SCALE + 1)],
            [1.00, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), 10 ** (Money::DEFAULT_SCALE + 1)],

            // Rounding tests
            [1, 3, 0],
            [2, 3, 1],
        ];
    }

    /**
     * @test
     * @dataProvider providesDivisionByZeroScenarios
     */
    public function testDivisionByZero(int|float|string $amount, int|float|string $operand)
    {
        $money = new Money($amount);
        $operandMoney = new Money($operand);

        $this->expectException(DivisionByZeroError::class);
        $money->divideBy($operand);
        $money->divideByMoney($operandMoney);
    }

    public function providesDivisionByZeroScenarios()
    {
        return [
            [0, 0],
            [1, 0],
            [PHP_FLOAT_MIN, PHP_FLOAT_MIN, 0],
            [PHP_FLOAT_MIN, -PHP_FLOAT_MIN, 0],
            [1, '0.00'],
        ];
    }

    /**
     * @test
     * @dataProvider providesAbsoluteScenarios
     */
    public function testAbsolute(int $expected, $amount)
    {
        $money = new Money($amount);

        $this->assertMoneyEqualsMoney(new Money($expected), $money->absolute());
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

        $this->assertMoneyEqualsMoney(new Money($expected), $money->invertSign());
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
     * @dataProvider providesIsZeroScenarios
     */
    public function testIsZeroAndNonZero(int|float|string $amount, bool $expectedIsZero)
    {
        $money = new Money($amount);
        $this->assertEquals($expectedIsZero, $money->isZero());
        $this->assertEquals(! $expectedIsZero, $money->isNonZero());
    }

    public function providesIsZeroScenarios()
    {
        return [
            '0 is zero' => [0, true],
            '1 is not zero' => [1, false],
            '-1 is not zero' => [-1, false],
            '0.00000 is zero' => [0.00000, true],
            '0.00001 is not zero' => [0.00001, false],
            '-0.0001 is not zero' => [-0.00001, false],
            '"0" is zero' => ['0', true],
            '"0.00" is zero' => ['0.00', true],
            '"-0" is zero' => ['-0', true],
            '"-0.00" is zero' => ['-0.00', true],
            'Max float is not zero' => [PHP_FLOAT_MAX, false],
            // Technically not zero, but smaller than our scale : 2.2250738585072014E-308
            'Near-zero float is zero' => [PHP_FLOAT_MIN, true],
            'Near-zero negative float is zero' => [PHP_FLOAT_MIN, true],
            'Near-zero string is zero' => [bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), true], // Rounded to 0
            'Near-zero negative string is zero' => [bcdiv(-1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), true], // Rounded to 0
            'Rounded up near-zero string is not zero' => [bcdiv(5, 10 ** (Money::DEFAULT_SCALE), Money::DEFAULT_SCALE), false],
            'Rounded down near-zero negative string is zero' => [bcdiv(-5, 10 ** (Money::DEFAULT_SCALE), Money::DEFAULT_SCALE), false],
        ];
    }

    /**
     * @test
     * @dataProvider providesIsPositiveScenarios
     */
    public function testIsPositive(int|float|string $amount, bool $expectedIsPositive)
    {
        $money = new Money($amount);
        $this->assertEquals($expectedIsPositive, $money->isPositive());
        $this->assertEquals(! $expectedIsPositive, $money->isNegative());
    }

    public function providesIsPositiveScenarios()
    {
        return [
            '1 is positive' => [1, true],
            '-1 is not positive' => [-1, false],
            '0 is positive' => [0, true],
            '0.00000 is positive' => [0.00000, true],
            '0.00001 is positive' => [0.00001, true],
            '-0.0001 is not positive' => [-0.00001, false],
            '"0" is positive' => ['0', true],
            '"0.00" is positive' => ['0.00', true],
            '"-0" is positive' => ['-0', true],
            '"-0.00" is positive' => ['-0.00', true],
            'Max int is positive' => [PHP_INT_MAX, true],
            'Min int is not positive' => [PHP_INT_MIN, false],
            'Max float is positive' => [PHP_FLOAT_MAX, true],
            'Min float is not positive' => [-PHP_FLOAT_MAX, false],
            'Near-zero positive float is positive' => [PHP_FLOAT_MIN, true],
            'Near-zero negative float is positive' => [-PHP_FLOAT_MIN, true],
            'Near-zero string is positive' => [bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), true], // Rounded to 0
            'Near-zero negative string is positive' => [bcdiv(-1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), true], // Rounded to 0
            'Rounded up near-zero string is positive' => [bcdiv(5, 10 ** (Money::DEFAULT_SCALE), Money::DEFAULT_SCALE), true],
            'Rounded down near-zero negative string is not positive' => [bcdiv(-5, 10 ** (Money::DEFAULT_SCALE), Money::DEFAULT_SCALE), false],
        ];
    }

    /**
     * @test
     * @dataProvider providesEqualityComparisonScenarios
     *
     * @param mixed $expected
     */
    public function testEqualityComparison(int|float|string $amount, int|float|string $operandAmount, bool $expectEquals)
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
            // Type checks
            [1234, 1234, true],
            [-1234, -1234, true],
            [1234, 1234.00, true],
            [-1234, -1234.00, true],
            [1234, '1234', true],
            [-1234, '-1234', true],
            [1234, '1234.00', true],
            [-1234, '-1234.00', true],

            // Different signs are not equal
            [1234, -1234, false],
            [1234, '-1234', false],

            // Zero edge cases
            [0, 0, true],
            [0, 0.00, true],
            [0, '0', true],
            [0, '0.00', true],
            [0, -0, true],
            [0, -0.00, true],
            [0, '-0', true],
            [0, '-0.00', true],
            [0, PHP_FLOAT_MIN, true],
            [0, -PHP_FLOAT_MIN, true],
            [0, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), true],
            [0, bcdiv(1, 10 ** (Money::DEFAULT_SCALE + 1), Money::DEFAULT_SCALE + 1), true],

            // Direct string comparison
            ['12.34', '12.34000', true],
            ['0', '0', true],
            ['0', '0.00', true],
            ['0', '-0.00', true],
        ];
    }

    /**
     * @test
     */
    public function testCopyDoesNotReferenceTheSameObject()
    {
        $original = new Money(123);
        $copy = $original->copy();

        $this->assertNotSame($original, $copy);
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

    /**
     * @test
     */
    public function testMultiplyByPrecision()
    {
        $money = new Money(100);
        $this->assertEquals(115, $money->multiplyBy('1.14975')->inCents());
        $this->assertEquals(114, $money->multiplyBy('1.14975', round:false)->inCents());
    }

    /**
     * @test
     */
    public function testDefaultRoundPrecision()
    {
        $money = new Money(12.34);
        $this->assertEquals('12', $money->round()->amount());
    }

    /**
     * @test
     * @dataProvider providesRoundingPrecisonScenarios
     */
    public function testRoundingPrecison(string $amount, int $precision, string $expected)
    {
        $money = new Money($amount);

        $this->assertEquals($expected, $money->round($precision)->amount());
    }

    public function providesRoundingPrecisonScenarios()
    {
        return [
            // Zero precision
            'do not round integers' => ['12', 0, '12'],
            'round down' => ['12.4', 0, '12'],
            'round up' => ['12.5', 0, '13'],
            'do not round negative integers' => ['-12', 0, '-12'],
            'round up negative' => ['-12.4', 0, '-12'],
            'round down negative' => ['-12.5', 0, '-13'],

            // Larger precision
            'precision is 1' => ['12.34567', 1, '12.3'],
            'precision is 2' => ['12.34567', 2, '12.35'],
            'precision is 3' => ['12.34567', 3, '12.346'],
            'precision is 1 negative' => ['-12.34567', 1, '-12.3'],
            'precision is 2 negative' => ['-12.34567', 2, '-12.35'],
            'precision is 3 negative' => ['-12.34567', 3, '-12.346'],
            'appends zeroes on higher precision' => ['12.34', 5, '12.34000'],
        ];
    }

    /**
     * @test
     */
    public function testInDollars()
    {
        $money = new Money('1234.56');
        $this->assertEquals('12.35', $money->inDollars());
    }
}
