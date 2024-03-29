<?php

namespace App\Repositories;

use App\Models\Address;
use App\Repositories\Interfaces\BookingRepositoryInterface;

class BookingRepository implements BookingRepositoryInterface
{
    public function getBookingByAddressId(int $addressId)
    {
        return Address::whereId($addressId)->with('bookings');
    }
}
