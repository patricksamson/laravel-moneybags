<?php

namespace PatrickSamson\LaravelMoneybags;

class Money
{
    private string $amount;

    public function __construct(int|float|string $amount = 0)
    {
        // TODO improve parsing?
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

    public function multiplyByMoney(Money $operand): self
    {
        return $this->newInstance(bcmul($this->amount, $operand->amount));
    }

    public function multiplyBy(string $multiplier): self
    {
        return $this->newInstance(bcmul($this->amount, $multiplier));
    }

    public function divideByMoney(Money $operand): self
    {
        return $this->newInstance(bcdiv($this->amount, $operand->amount));
    }

    public function divideBy(string $divisor): self
    {
        return $this->newInstance(bcdiv($this->amount, $divisor));
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

    public function isEqualTo(Money $operand)
    {
        return $this->amount === $operand->amount;
    }

    public function isNotEqualTo(Money $operand)
    {
        return $this->amount !== $operand->amount;
    }

    public function clone(): self
    {
        return clone $this;
    }

    private function newInstance(string $amount): self
    {
        return new self($amount);
    }
}
