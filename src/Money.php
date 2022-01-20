<?php

namespace PatrickSamson\LaravelMoneybags;

class Money
{
    public const DEFAULT_SCALE = 4;

    /**
     * The amount, in cents.
     */
    private string $amount;

    public function __construct(int|float|string $amount = 0)
    {
        if (is_float($amount)) {
            // Correctly parse floats containing exponential numbers.
            $amount = number_format($amount, self::DEFAULT_SCALE, '.', '');
        }
        $this->amount = (string) $amount;
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
            // Correctly parse floats containing exponential numbers.
            $amount = number_format($amount, self::DEFAULT_SCALE, '.', '');
        }

        return new self(bcmul((string) $amount, 100));
    }

    public static function zero(): self
    {
        return new self();
    }

    public function add(Money $operand): self
    {
        return $this->newInstance(bcadd($this->amount, $operand->amount));
    }

    public function subtract(Money $operand): self
    {
        return $this->newInstance(bcsub($this->amount, $operand->amount));
    }

    public function multiplyByMoney(Money $operand, bool $round = true): self
    {
        $result = bcmul($this->amount, $operand->amount, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function multiplyBy(string $multiplier, bool $round = true): self
    {
        $result = bcmul($this->amount, $multiplier, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function divideByMoney(Money $operand, bool $round = true): self
    {
        $result = bcdiv($this->amount, $operand->amount, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function divideBy(string $divisor, bool $round = true): self
    {
        $result = bcdiv($this->amount, $divisor, self::DEFAULT_SCALE);

        return $this->newInstance($round ? $this->bcRound($result) : $result);
    }

    public function absolute(): self
    {
        return str_starts_with($this->amount, '-') ? $this->invertSign() : $this->clone();
    }

    public function invertSign(): self
    {
        return $this->multiplyBy(-1);
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, 0) === 0;
    }

    public function isNonZero(): bool
    {
        return bccomp($this->amount, 0) !== 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, 0) >= 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, 0) < 0;
    }

    public function isEqualTo(Money $operand): bool
    {
        return $this->amount === $operand->amount;
    }

    public function isNotEqualTo(Money $operand): bool
    {
        return $this->amount !== $operand->amount;
    }

    public function round(int $precision = 0)
    {
        return $this->newInstance($this->bcRound($this->amount, $precision));
    }

    public function clone(): self
    {
        return clone $this;
    }

    private static function bcRound(string $amount, int $precision = 0): string
    {
        if (strpos($amount, '.') !== false) {
            if ($amount[0] != '-') {
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
