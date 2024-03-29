<?php

namespace App\Repositories\Interfaces;

interface BookingRepositoryInterface
{
    public function getBookingByAddressId(int $addressId);
}
