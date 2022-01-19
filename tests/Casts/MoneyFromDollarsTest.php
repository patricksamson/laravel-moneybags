<?php

namespace PatrickSamson\LaravelMoneybags\Tests;

use PatrickSamson\LaravelMoneybags\Casts\MoneyFromDollars;
use PatrickSamson\LaravelMoneybags\Money;
use PatrickSamson\LaravelMoneybags\Tests\Concerns\AssertsMoney;
use PHPUnit\Framework\TestCase;

class MoneyFromDollarsTest extends TestCase
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
        $cast = new MoneyFromDollars();
        $attribute = $cast->get(null, null, $value, []);

        $this->assertInstanceOf(Money::class, $attribute);
        $this->assertMoneyEqualsMoney(Money::fromDollars($value), $attribute);

        $raw = $cast->set(null, null, $attribute, []);
        $this->assertEquals($value, $raw);
    }

    public function providesCastScenarios()
    {
        return [
            [12.34],
            ['12.34'],
        ];
    }

    /**
     * @test
     */
    public function testHandlesNullValues()
    {
        $cast = new MoneyFromDollars();
        $value = null;
        $attribute = $cast->get(null, null, $value, []);

        $this->assertNull($attribute);

        $raw = $cast->set(null, null, $attribute, []);
        $this->assertNull($raw);
    }
}
