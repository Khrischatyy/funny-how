<?php

namespace App\Services;

use App\Exceptions\OperatingHourException;
use App\Models\OperatingHour;
use Exception;
use Illuminate\Support\Facades\DB;

class OperatingHourService
{
    public function everyday($address_id, $mode, $open_time, $close_time)
    {
        OperatingHour::where('address_id', $address_id)->delete();

        return OperatingHour::create([
            'address_id' => $address_id,
            'mode_id' => $mode,
            'open_time' => $open_time,
            'close_time' => $close_time,
        ]);
    }


    public function permanent($address_id, $mode)
    {
        OperatingHour::where('address_id', $address_id)->delete();

        return OperatingHour::create([
            'address_id' => $address_id,
            'mode_id' => $mode,
            'open_time' => '00:00',
            'close_time' => '24:00'
        ]);
    }


    public function regular(
        $address_id,
        $open_time_weekday,
        $close_time_weekday,
        $open_time_weekend,
        $close_time_weekend)
    {
        OperatingHour::where('address_id', $address_id)->delete();

        $weekdays_hours = $this->weekdays($address_id, $open_time_weekday, $close_time_weekday);
        $weekends_hours = $this->weekends($address_id, $open_time_weekend, $close_time_weekend);

        return array_merge($weekdays_hours, $weekends_hours);
    }

    private function weekdays($address_id, $open_time, $close_time)
    {
        $inserts = [];

        for ($day = 1; $day <= 5; $day++) {
            $inserts[] = [
                'address_id' => $address_id,
                'mode_id' => 4,
                'day_of_week' => $day,
                'open_time' => $open_time,
                'close_time' => $close_time
            ];
        }

        $isHoursAdded = OperatingHour::insert($inserts);

        if (!$isHoursAdded) {
            throw new OperatingHourException("Failed to add weekDAYS hours.");
        }

        return $inserts;
    }

    private function weekends($address_id, $open_time, $close_time )
    {

        $inserts = [
            [
                'address_id' => $address_id,
                'mode_id' => 3,
                'day_of_week' => 0,
                'open_time' => $open_time,
                'close_time' => $close_time
            ],
            [
                'address_id' => $address_id,
                'mode_id' => 3,
                'day_of_week' => 6,
                'open_time' => $open_time,
                'close_time' => $close_time
            ],
        ];

        $isHoursAdded = OperatingHour::insert($inserts);

        if (!$isHoursAdded) {
            throw new OperatingHourException("Failed to add weekENDS hours.");
        }

        return $inserts;
    }


    public function specificDay()
    {}
}
