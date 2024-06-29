<?php

namespace App\Services;

use App\Models\OperatingHour;

class OperatingHourService
{
    public function everyday($address_id, $mode, $open_time, $close_time)
    {
        $this->clearExistingHours($address_id);

        return OperatingHour::create([
            'address_id' => $address_id,
            'mode_id' => $mode,
            'open_time' => $open_time,
            'close_time' => $close_time,
        ]);
    }

    public function permanent($address_id, $mode)
    {
        $this->clearExistingHours($address_id);

        return OperatingHour::create([
            'address_id' => $address_id,
            'mode_id' => $mode,
            'open_time' => '00:00',
            'close_time' => '24:00'
        ]);
    }

    public function specificDay($address_id, $mode, $hours)
    {
        $this->clearExistingHours($address_id);

        // Массив для вставки новых записей
        $inserts = [];

        foreach ($hours as $dayData) {
            $inserts[] = [
                'address_id' => $address_id,
                'mode_id' => $mode,
                'day_of_week' => $dayData['day_of_week'],
                'open_time' => $dayData['open_time'],
                'close_time' => $dayData['close_time'],
                'is_closed' => $dayData['is_closed'] ?? false,
            ];
        }

        // Выполняем массовую вставку
        OperatingHour::insert($inserts);

        return OperatingHour::where('address_id', $address_id)->get();
    }

    public function getOperatingHours($address_id)
    {
        return OperatingHour::where('address_id', $address_id)->get();
    }

    private function clearExistingHours($address_id)
    {
        OperatingHour::where('address_id', $address_id)->delete();
    }
}