<?php

namespace App\Services;

use App\Models\OperatingHour;
use Carbon\Carbon;

class OperatingHourService
{
    public function everyday($address_id, $mode, $open_time, $close_time)
    {
        $this->clearExistingHours($address_id);

        return OperatingHour::create([
            'address_id' => $address_id,
            'mode_id' => $mode,
            'open_time' => Carbon::createFromFormat('H:i', $open_time)->toTimeString(),
            'close_time' => Carbon::createFromFormat('H:i', $close_time)->toTimeString(),
        ]);
    }

    public function permanent($address_id, $mode)
    {
        $this->clearExistingHours($address_id);

        return OperatingHour::create([
            'address_id' => $address_id,
            'mode_id' => $mode,
            'open_time' => Carbon::now()->startOfDay()->toTimeString(), // 00:00
            'close_time' => '23:59:59', // представляем 24:00 как 23:59:59
        ]);
    }

    public function specificDay($address_id, $mode, $hours)
    {
        $this->clearExistingHours($address_id);

        // Массив для вставки новых записей
        $inserts = [];

        foreach ($hours as $dayData) {
            $open_time = Carbon::parse($dayData['open_time'])->toTimeString();
            $close_time = $dayData['close_time'] === '24:00' ? '23:59:59' : Carbon::parse($dayData['close_time'])->toTimeString();

            $inserts[] = [
                'address_id' => $address_id,
                'mode_id' => $mode,
                'day_of_week' => $dayData['day_of_week'],
                'open_time' => $open_time,
                'close_time' => $close_time,
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