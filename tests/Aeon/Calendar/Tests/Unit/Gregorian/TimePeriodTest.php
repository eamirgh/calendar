<?php

declare(strict_types=1);

namespace Aeon\Calendar\Tests\Unit\Gregorian;

use Aeon\Calendar\Gregorian\DateTime;
use Aeon\Calendar\Gregorian\TimePeriod;
use Aeon\Calendar\TimeUnit;
use PHPUnit\Framework\TestCase;

final class TimePeriodTest extends TestCase
{
    public function test_distance_in_time_unit_from_start_to_end_date() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 00:00:00.0000'),
            DateTime::fromString('2020-01-02 00:00:00.0000')
        );

        $this->assertSame(86400, $period->distance()->inSeconds());
        $this->assertFalse($period->distance()->isNegative());
    }

    public function test_precise_distance_in_time_unit_from_start_to_end() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 12:25:30.079635'),
            DateTime::fromString('2020-01-01 12:25:32.588460')
        );

        $this->assertSame("2.508825", $period->distance()->inSecondsPreciseString());
    }

    public function test_precise_distance_in_time_unit_from_start_to_end_backward() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 12:25:30.079635'),
            DateTime::fromString('2020-01-01 12:25:32.588460')
        );

        $this->assertSame("-2.508825", $period->distanceBackward()->inSecondsPreciseString());
    }

    public function test_distance_in_time_unit_from_start_to_end_date_between_years() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 00:00:00.0000'),
            DateTime::fromString('2021-01-01 00:00:00.0000')
        );

        $this->assertSame(DateTime::fromString('2020-01-01 00:00:00.0000')->year()->numberOfDays(), $period->distance()->inDays());
        $this->assertFalse($period->distance()->isNegative());
        $this->assertTrue(DateTime::fromString('2020-01-01 00:00:00.0000')->year()->isLeap());
    }

    public function test_distance_in_time_unit_from_end_to_start_date() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 00:00:00.0000'),
            DateTime::fromString('2020-01-02 00:00:00.0000')
        );

        $this->assertSame(-86400, $period->distanceBackward()->inSeconds());
        $this->assertSame(86400, $period->distanceBackward()->inSecondsAbs());
        $this->assertTrue($period->distanceBackward()->isNegative());
    }

    public function test_iterating_through_day_by_hour() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 00:00:00.0000'),
            DateTime::fromString('2020-01-02 00:00:00.0000')
        );

        $timePeriods = $period->iterate(TimeUnit::hour());

        $this->assertCount(24, $timePeriods);

        $this->assertInstanceOf(TimePeriod::class, $timePeriods[0]);
        $this->assertInstanceOf(TimePeriod::class, $timePeriods[1]);
        $this->assertInstanceOf(TimePeriod::class, $timePeriods[2]);
        $this->assertInstanceOf(TimePeriod::class, $timePeriods[23]);

        $this->assertSame(0, $timePeriods[0]->start()->time()->hour());
        $this->assertSame(1, $timePeriods[0]->end()->time()->hour());
        $this->assertSame(1, $timePeriods[1]->start()->time()->hour());
        $this->assertSame(2, $timePeriods[1]->end()->time()->hour());
        $this->assertSame(2, $timePeriods[2]->start()->time()->hour());
        $this->assertSame(3, $timePeriods[2]->end()->time()->hour());
        $this->assertSame(23, $timePeriods[23]->start()->time()->hour());
        $this->assertSame(0, $timePeriods[23]->end()->time()->hour());
    }

    public function test_iterating_through_day_backward_by_hour() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 00:00:00.0000'),
            DateTime::fromString('2020-01-02 00:00:00.0000')
        );

        $timePeriods = $period->iterateBackward(TimeUnit::hour());

        $this->assertCount(24, $timePeriods);

        $this->assertInstanceOf(TimePeriod::class, $timePeriods[0]);
        $this->assertInstanceOf(TimePeriod::class, $timePeriods[1]);
        $this->assertInstanceOf(TimePeriod::class, $timePeriods[2]);
        $this->assertInstanceOf(TimePeriod::class, $timePeriods[23]);

        $this->assertSame(0, $timePeriods[0]->start()->time()->hour());
        $this->assertSame(23, $timePeriods[0]->end()->time()->hour());
        $this->assertSame(23, $timePeriods[1]->start()->time()->hour());
        $this->assertSame(22, $timePeriods[1]->end()->time()->hour());
        $this->assertSame(22, $timePeriods[2]->start()->time()->hour());
        $this->assertSame(21, $timePeriods[2]->end()->time()->hour());
        $this->assertSame(1, $timePeriods[23]->start()->time()->hour());
        $this->assertSame(0, $timePeriods[23]->end()->time()->hour());
    }

    public function test_iterating_through_day_by_2_days() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 00:00:00.0000'),
            DateTime::fromString('2020-01-02 00:00:00.0000')
        );

        $timePeriods = $period->iterate(TimeUnit::days(2));

        $this->assertCount(1, $timePeriods);
    }

    public function test_iterating_through_day_backward_by_2_days() : void
    {
        $period = new TimePeriod(
            DateTime::fromString('2020-01-01 00:00:00.0000'),
            DateTime::fromString('2020-01-02 00:00:00.0000')
        );

        $timePeriods = $period->iterateBackward(TimeUnit::days(2));

        $this->assertCount(1, $timePeriods);
    }
}
