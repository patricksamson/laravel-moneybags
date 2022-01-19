<?php

namespace PatrickSamson\LaravelMoneybags\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PatrickSamson\LaravelMoneybags\Money;

class MoneyFromCents implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \PatrickSamson\LaravelMoneybags\Money
     */
    public function get($model, $key, $value, $attributes)
    {
        return ! is_null($value) ? Money::fromCents($value) : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  \PatrickSamson\LaravelMoneybags\Money  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value !== null && ! $value instanceof Money) {
            throw new InvalidArgumentException('The given value is not a Money instance.');
        }

        return optional($value)->inCents();
    }
}
