<?php

namespace PatrickSamson\LaravelMoneybags;

class Money
{
    /**
     * Represents the default precision at which BCMath will execute the arithmetic operations.
     */
    public const DEFAULT_SCALE = 6;

    /**
     * The amount, in cents.
     */
    private string $amount;

    /**
     * Creates a new Money instance.
     */
    public function __construct(int|float|string $amount = 0)
    {
        if (is_float($amount)) {
            $amount = static::parseFloat($amount);
        }
        $this->amount = $amount;
    }

    /**
     * Getter for the raw amount.
     */
    public function amount(): string
    {
        return $this->amount;
    }

    /**
     * Return the amount in cents as an integer.
     */
    public function inCents(): int
    {
        return (int) $this->amount;
    }

    /**
     * Return the amount in dollars, including cents.
     */
    public function inDollars(): string
    {
        return number_format(bcdiv($this->amount, '100', 3), 2, '.', '');
    }

    /**
     * Helper to create a Money object from a cent amount.
     */
    public static function fromCents(int|float|string $amount): self
    {
        return new self($amount);
    }

    /**
     * Helper to create a Money object from a dollar amount.
     */
    public static function fromDollars(int|float|string $amount): self
    {
        if (is_float($amount)) {
            $amount = static::parseFloat($amount);
        }

        return new self(bcmul($amount, 100));
    }

    /**
     * Helper for a zero value Money object.
     */
    public static function zero(): self
    {
        return new self();
    }

    /**
     * Adds another amount to this amount.
     */
    public function add(Money $operand): self
    {
        return $this->newInstance(bcadd($this->amount, $operand->amount, self::DEFAULT_SCALE));
    }

    /**
     * Substracts another amount from this amount.
     */
    public function subtract(Money $operand): self
    {
        return $this->newInstance(bcsub($this->amount, $operand->amount, self::DEFAULT_SCALE));
    }

    /**
     * Multiplies this amount by another Money operand.
     */
    public function multiplyByMoney(Money $operand, bool $round = true): self
    {
        $result = bcmul($this->amount, $operand->amount, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    /**
     * Multiplies this amount by the given scale factor.
     */
    public function multiplyBy(int|float|string $multiplier, bool $round = true): self
    {
        if (is_float($multiplier)) {
            $multiplier = static::parseFloat($multiplier);
        }

        $result = bcmul($this->amount, $multiplier, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    /**
     * Divides this amount by another Money operand.
     */
    public function divideByMoney(Money $operand, bool $round = true): self
    {
        $result = bcdiv($this->amount, $operand->amount, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    /**
     * Divides this amount by the given scale factor.
     */
    public function divideBy(int|float|string $divisor, bool $round = true): self
    {
        if (is_float($divisor)) {
            $divisor = static::parseFloat($divisor);
        }

        $result = bcdiv($this->amount, $divisor, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    /**
     * Return the absolute value of this amount.
     */
    public function absolute(): self
    {
        return str_starts_with($this->amount, '-') ? $this->invertSign() : $this->copy();
    }

    /**
     * Inverts the sign of this amount.
     */
    public function invertSign(): self
    {
        return $this->multiplyBy(-1);
    }

    /**
     * Determine if the amount is zero.
     */
    public function isZero(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) === 0;
    }

    /**
     * Determine if the amount is different than zero.
     */
    public function isNonZero(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) !== 0;
    }
    /**
     * Determine if the amount is positive.
     */
    public function isPositive(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) >= 0;
    }

    /**
     * Determine if the amount is negative.
     */
    public function isNegative(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) < 0;
    }

    /**
     * Determine if this amount is equal to the given amount.
     */
    public function isEqualTo(Money $operand): bool
    {
        return bccomp($this->amount, $operand->amount, self::DEFAULT_SCALE) === 0;
    }

    /**
     * Determine if this amount is different than the given amount.
     */
    public function isNotEqualTo(Money $operand): bool
    {
        return bccomp($this->amount, $operand->amount, self::DEFAULT_SCALE) !== 0;
    }

    public function round(int $precision = 0)
    {
        return $this->newInstance($this->bcRound($this->amount, $precision));
    }

    /**
     * Copy this instance of Money to a new instance.
     */
    public function copy(): self
    {
        return clone $this;
    }

    /**
     * Correctly parse float into a string, handling exponential numbers.
     */
    private static function parseFloat(float $amount): string
    {
        return number_format($amount, self::DEFAULT_SCALE, '.', '');
    }

    /**
     * Rounds an amount to a given precision.
     * This doesn't exist in BCMath, as the extension only truncates numbers.
     */
    private static function bcRound(string $amount, int $precision = 0): string
    {
        if (str_contains($amount, '.')) {
            if (! str_starts_with($amount, '-')) {
                return bcadd($amount, '0.' . str_repeat('0', $precision) . '5', $precision);
            }

            return bcsub($amount, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return $amount;
    }

    /**
     * Create a new Money instance from the given amount.
     */
    private function newInstance(string $amount): self
    {
        return new self($amount);
    }
}
