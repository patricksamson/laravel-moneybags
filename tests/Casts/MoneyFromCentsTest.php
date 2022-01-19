<?php

namespace PatrickSamson\LaravelMoneybags\Tests;

use PatrickSamson\LaravelMoneybags\Casts\MoneyFromCents;
use PatrickSamson\LaravelMoneybags\Money;
use PatrickSamson\LaravelMoneybags\Tests\Concerns\AssertsMoney;
use PHPUnit\Framework\TestCase;

class MoneyFromCentsTest extends TestCase
{
    use AssertsMoney;

    /**
     * @test
     * @dataProvider providesCastScenarios
     *
     * @param mixed $value
     */
    public function testGetsAndSetsAttribute($value)
    {
        $cast = new MoneyFromCents();
        $attribute = $cast->get(null, null, $value, []);

        $this->assertInstanceOf(Money::class, $attribute);
        $this->assertMoneyEqualsMoney(Money::fromCents($value), $attribute);

        $raw = $cast->set(null, null, $attribute, []);
        $this->assertEquals($value, $raw);
    }

    public function providesCastScenarios()
    {
        return [
            [1234],
            ['1234'],
        ];
    }

    /**
     * @test
     */
    public function testHandlesNullValues()
    {
        $cast = new MoneyFromCents();
        $value = null;
        $attribute = $cast->get(null, null, $value, []);

        $this->assertNull($attribute);

        $raw = $cast->set(null, null, $attribute, []);
        $this->assertNull($raw);
    }
}
