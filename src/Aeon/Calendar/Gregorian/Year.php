<?php

declare(strict_types=1);

namespace Aeon\Calendar\Gregorian;

use Aeon\Calendar\Exception\InvalidArgumentException;

/**
 * @psalm-immutable
 */
final class Year
{
    private int $year;

    private YearMonths $months;

    public function __construct(int $year)
    {
        $this->year = $year;
        $this->months = new YearMonths($this);
    }

    /**
     * @psalm-pure
     * @psalm-suppress ImpureMethodCall
     */
    public static function fromDateTime(\DateTimeInterface $dateTime) : self
    {
        return new self((int) $dateTime->format('Y'));
    }

    public static function fromString(string $date) : self
    {
        return self::fromDateTime(new \DateTimeImmutable($date));
    }

    /**
     * @return array{year:int}
     */
    public function __debugInfo() : array
    {
        return [
            'year' => $this->year,
        ];
    }

    public function january() : Month
    {
        return $this->months()->byNumber(1);
    }

    public function february() : Month
    {
        return $this->months()->byNumber(2);
    }

    public function march() : Month
    {
        return $this->months()->byNumber(3);
    }

    public function april() : Month
    {
        return $this->months()->byNumber(4);
    }

    public function may() : Month
    {
        return $this->months()->byNumber(5);
    }

    public function june() : Month
    {
        return $this->months()->byNumber(6);
    }

    public function july() : Month
    {
        return $this->months()->byNumber(7);
    }

    public function august() : Month
    {
        return $this->months()->byNumber(8);
    }

    public function september() : Month
    {
        return $this->months()->byNumber(9);
    }

    public function october() : Month
    {
        return $this->months()->byNumber(10);
    }

    public function november() : Month
    {
        return $this->months()->byNumber(11);
    }

    public function december() : Month
    {
        return $this->months()->byNumber(12);
    }

    public function months() : YearMonths
    {
        return $this->months;
    }

    public function number() : int
    {
        return $this->year;
    }

    public function plus(int $years) : self
    {
        return self::fromDateTime($this->toDateTimeImmutable()->modify(\sprintf('+%d year', $years)));
    }

    public function minus(int $years) : self
    {
        return self::fromDateTime($this->toDateTimeImmutable()->modify(\sprintf('-%d year', $years)));
    }

    public function next() : self
    {
        return self::fromDateTime($this->toDateTimeImmutable()->modify('+1 year'));
    }

    public function previous() : self
    {
        return self::fromDateTime($this->toDateTimeImmutable()->modify('-1 year'));
    }

    public function numberOfMonths() : int
    {
        return 12;
    }

    public function numberOfDays() : int
    {
        return $this->isLeap() ? 366 : 365;
    }

    /**
     * @param callable(Day $day) : void $iterator
     *
     * @return array<mixed>
     */
    public function mapDays(callable $iterator) : array
    {
        return \array_map(
            $iterator,
            \array_merge(
                ...\array_map(
                    fn (int $month) : array => $this->months()->byNumber($month)->days()->all(),
                    \range(1, 12)
                )
            )
        );
    }

    /**
     * @param callable(Day $day) : bool $iterator
     *
     * @return Days
     */
    public function filterDays(callable $iterator) : Days
    {
        return new Days(...\array_filter(
            \array_merge(
                ...\array_map(
                    fn (int $month) : array => $this->months()->byNumber($month)->days()->all(),
                    \range(1, 12)
                )
            ),
            $iterator
        ));
    }

    public function isLeap() : bool
    {
        return (bool) $this->toDateTimeImmutable()->format('L');
    }

    public function toDateTimeImmutable() : \DateTimeImmutable
    {
        return new \DateTimeImmutable(\sprintf('%d-01-01 00:00:00.000000 UTC', $this->number()));
    }

    public function isEqual(self $year) : bool
    {
        return $this->number() === $year->number();
    }

    public function isBefore(self $year) : bool
    {
        return $this->number() < $year->number();
    }

    public function isBeforeOrEqual(self $year) : bool
    {
        return $this->number() <= $year->number();
    }

    public function isAfter(self $year) : bool
    {
        return $this->number() > $year->number();
    }

    public function isAfterOrEqual(self $year) : bool
    {
        return $this->number() >= $year->number();
    }

    public function iterate(self $destination) : Years
    {
        return $this->isAfter($destination)
            ? $this->since($destination)
            : $this->until($destination);
    }

    public function until(self $month) : Years
    {
        if ($this->isAfter($month)) {
            throw new InvalidArgumentException(
                \sprintf(
                    '%d is after %d',
                    $this->number(),
                    $month->number(),
                )
            );
        }

        return new Years(
            ...\array_map(
                function (\DateTimeImmutable $dateTimeImmutable) : self {
                    return self::fromDateTime($dateTimeImmutable);
                },
                \iterator_to_array(
                    new \DatePeriod(
                        $this->toDateTimeImmutable(),
                        new \DateInterval('P1Y'),
                        $month->toDateTimeImmutable()
                    )
                )
            )
        );
    }

    public function since(self $month) : Years
    {
        if ($this->isBefore($month)) {
            throw new InvalidArgumentException(
                \sprintf(
                    '%d is before %d',
                    $this->number(),
                    $month->number(),
                )
            );
        }

        $interval = new \DateInterval('P1Y');
        /** @psalm-suppress ImpurePropertyAssignment */
        $interval->invert = 1;

        return new Years(
            ...\array_map(
                function (\DateTimeImmutable $dateTimeImmutable) : self {
                    return self::fromDateTime($dateTimeImmutable);
                },
                \array_reverse(
                    \iterator_to_array(
                        new \DatePeriod(
                            $month->toDateTimeImmutable(),
                            $interval,
                            $this->toDateTimeImmutable()
                        )
                    )
                )
            )
        );
    }
}
