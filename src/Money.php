<?php

namespace PatrickSamson\LaravelMoneybags;

class Money
{
    public const DEFAULT_SCALE = 6;

    /**
     * The amount, in cents.
     */
    private string $amount;

    public function __construct(int|float|string $amount = 0)
    {
        if (is_float($amount)) {
            $amount = static::parseFloat($amount);
        }
        $this->amount = $amount;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function inCents(): int
    {
        return (int) $this->amount;
    }

    public function inDollars(): string
    {
        return number_format(bcdiv($this->amount, '100', 3), 2, '.', '');
    }

    public static function fromCents(int|float|string $amount): self
    {
        return new self($amount);
    }

    public static function fromDollars(int|float|string $amount): self
    {
        if (is_float($amount)) {
            $amount = static::parseFloat($amount);
        }

        return new self(bcmul($amount, 100));
    }

    public static function zero(): self
    {
        return new self();
    }

    public function add(Money $operand): self
    {
        return $this->newInstance(bcadd($this->amount, $operand->amount, self::DEFAULT_SCALE));
    }

    public function subtract(Money $operand): self
    {
        return $this->newInstance(bcsub($this->amount, $operand->amount, self::DEFAULT_SCALE));
    }

    public function multiplyByMoney(Money $operand, bool $round = true): self
    {
        $result = bcmul($this->amount, $operand->amount, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function multiplyBy(int|float|string $multiplier, bool $round = true): self
    {
        if (is_float($multiplier)) {
            $multiplier = static::parseFloat($multiplier);
        }

        $result = bcmul($this->amount, $multiplier, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function divideByMoney(Money $operand, bool $round = true): self
    {
        $result = bcdiv($this->amount, $operand->amount, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function divideBy(int|float|string $divisor, bool $round = true): self
    {
        if (is_float($divisor)) {
            $divisor = static::parseFloat($divisor);
        }

        $result = bcdiv($this->amount, $divisor, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function absolute(): self
    {
        return str_starts_with($this->amount, '-') ? $this->invertSign() : $this->copy();
    }

    public function invertSign(): self
    {
        return $this->multiplyBy(-1);
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) === 0;
    }

    public function isNonZero(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) !== 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) >= 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, 0, self::DEFAULT_SCALE) < 0;
    }

    public function isEqualTo(Money $operand): bool
    {
        return bccomp($this->amount, $operand->amount, self::DEFAULT_SCALE) === 0;
    }

    public function isNotEqualTo(Money $operand): bool
    {
        return bccomp($this->amount, $operand->amount, self::DEFAULT_SCALE) !== 0;
    }

    public function round(int $precision = 0)
    {
        return $this->newInstance($this->bcRound($this->amount, $precision));
    }

    public function copy(): self
    {
        return clone $this;
    }

    private static function parseFloat(float $amount): string
    {
        // Correctly parse floats containing exponential numbers.
        return number_format($amount, self::DEFAULT_SCALE, '.', '');
    }

    private static function bcRound(string $amount, int $precision = 0): string
    {
        $precision = max($precision, 0); // Prevent negative precision.
        if (str_contains($amount, '.')) {
            if (! str_starts_with($amount, '-')) {
                return bcadd($amount, '0.' . str_repeat('0', $precision) . '5', $precision);
            }

            return bcsub($amount, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return $amount;
    }

    private function newInstance(string $amount): self
    {
        return new self($amount);
    }
}
