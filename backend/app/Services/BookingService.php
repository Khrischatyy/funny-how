<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;

class BookingService
{
    public function __construct(public BookingRepository $bookingRepository)
    {}

    public function getFreeSlots(int $addressId,Carbon $day): array
    {
        $addressWithBookings = $this->bookingRepository->getBookingByAddressId($addressId)->first();

        $works_since = $day->copy()->setTimeFrom($addressWithBookings->works_since);
        $works_till = $day->copy()->setTimeFrom($addressWithBookings->works_till);

        $reservedTime = $addressWithBookings->bookings->map(function ($booking) {
            return $this->getWorkingTime(Carbon::create($booking->from), Carbon::create($booking->to));
        })->flatten(1)->toArray();

        $workingTimes = $this->getWorkingTime($works_since, $works_till);

        $freeSlots = [];

        foreach ($workingTimes as $workingTime) {
            if (!in_array($workingTime, $reservedTime)) {
                $freeSlots[] = $workingTime;
            }
        }

        return $freeSlots;
    }

    private function getWorkingTime($since, $till): array
    {
        $countAvailableHours = $since->diffInHours($till);

        $workingTime = [[$since, $since->copy()->addHour()]];

        for ($i = 0; $i < $countAvailableHours - 1; $i++) {

            $prevTime = $workingTime[$i];

            $workingTime[] = [$prevTime[0]->copy()->addHour(), $prevTime[1]->copy()->addHour()];
        };

        return $workingTime;
    }
}
