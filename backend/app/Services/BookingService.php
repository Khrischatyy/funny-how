<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Booking;
use App\Models\OperatingHour;
use App\Models\StudioClosure;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function getAllReservations(ReservationRequest $request): array
    {
        $date = $request->input('date');
        $addressId = $request->input('address_id');
        $bookings = Booking::where('address_id', $addressId)
            ->where('date', $date)
            ->orderBy('start_time', 'asc')
            ->get();

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $operatingHours = $this->getOperatingHours($addressId, $dayOfWeek);

        $openTime = Carbon::parse($date . ' ' . $operatingHours->open_time);
        $closeTime = Carbon::parse($date . ' ' . $operatingHours->close_time);


        return $this->calculateAvailableSlots($bookings, $openTime, $closeTime);
    }

    private function calculateAvailableSlots($bookings, $openTime, $closeTime): array
    {
        $availableSlots = [];
        $previousEndTime = $openTime;

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_time);
            $bookingEnd = Carbon::parse($booking->end_time);

            if ($previousEndTime->lt($bookingStart)) {
                $availableSlots[] = [
                    'start_time' => $previousEndTime->format('H:i'),
                    'end_time' => $bookingStart->format('H:i')
                ];
            }
            $previousEndTime = $bookingEnd;
        }

        if ($previousEndTime->lt($closeTime)) {
            $availableSlots[] = [
                'start_time' => $previousEndTime->format('H:i'),
                'end_time' => $closeTime->format('H:i')
            ];
        }

        return $availableSlots;
    }


    public function getTotalCost(string $startTime, string $endTime, int $addressId)
    {

        // Расчет количества часов между start_time и end_time

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $hours = $end->diffInHours($start);


        $addressPrices = DB::table('address_prices')
            ->where('address_id', $addressId)
            ->where('is_enabled', true)
            ->orderBy('hours', 'asc')
            ->get();

        if ($addressPrices->isEmpty()) {
            throw new \Exception('Сlock packages were not installed');
        }

        // Поиск подходящего пакета часов и расчет цены
        $totalPrice = 0;
        foreach ($addressPrices as $price) {
            if ($hours >= $price->hours) {
                $totalPrice = $hours * $price->price_per_hour;
            }
        }

        return $totalPrice;
    }

    public function bookAddress(BookingRequest $request): Booking
    {
        $addressId = $request->input('address_id');
        $bookingDate = Carbon::parse($request->input('date'));
        $startTime = Carbon::parse($request->input('start_time'));
        $endTime = Carbon::parse($request->input('end_time'));
        $userWhoBooks = Auth::user();

        $this->validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime);

        return Booking::create([
            'address_id' => $addressId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'user_id' => $userWhoBooks->id,
            'total_cost' => $this->getTotalCost($startTime, $endTime, $addressId),
            'date' => $bookingDate->format('Y-m-d'),
        ]);
    }

    private function validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime)
    {
        if (StudioClosure::where('address_id', $addressId)->where('closure_date', $bookingDate->toDateString())->exists()) {
            throw new BookingException('Studio is closed on this date', 422);
        }

        $operatingHours = $this->getOperatingHours($addressId, $bookingDate);

        if (!$operatingHours || $operatingHours->is_closed) {
            throw new BookingException('Booking times are outside of business hours.', 422);
        }

        if ($startTime->lt($operatingHours->open_time) || $endTime->gt($operatingHours->close_time)) {
            throw new BookingException("Booking times are outside of business hours. Studio opens at {$operatingHours->open_time} and closes at {$operatingHours->close_time}", 422);
        }

        if ($this->isTimeSlotTaken($addressId, $startTime, $endTime)) {
            throw new BookingException('Studio is already booked for the requested time slot', 400);
        }
    }

    private function isTimeSlotTaken($addressId, $startTime, $endTime): bool
    {
        return Booking::where('address_id', $addressId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    private function getOperatingHours($addressId,$bookingDate, $modeId = null)
    {

        $operatingHours = OperatingHour::where('address_id', $addressId)->get();

        $firstLineOperatingHours = $operatingHours->first();

        //1,2 mode - имеют только одну запись в базе об operating hours
        //3,4 это моды weekdays и weekends
        //надо будет переделать, че то тут хуйня какая-то

        return match ($firstLineOperatingHours->mode_id) {
            1, 2 => $operatingHours->first(),
            3, 4 => $this->regular($operatingHours, $bookingDate->dayOfWeek)->first(),
        };

    }

    private function regular($operatingHours, $dayOfWeek)
    {
        return $operatingHours->where('day_of_week', $dayOfWeek);
    }



}
