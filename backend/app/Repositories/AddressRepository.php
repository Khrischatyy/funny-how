<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\City;
use App\Models\Company;
use App\Models\OperatingHour;
use App\Repositories\Interfaces\AddressRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AddressRepository implements AddressRepositoryInterface
{
    public function getAddressByCityId(int $cityId): Collection
    {
        $addresses = Address::where('city_id', $cityId)
            ->with(['badges', 'rooms', 'rooms.photos', 'rooms', 'rooms.prices', 'company', 'operatingHours'])
            ->get()->filter(function ($address) {
                return $address->is_complete;
            })->values();

        foreach ($addresses as $address) {
            if ($address->company->logo) {
                $address->company->logo_url = Storage::disk('s3')->url($address->company->logo);
            } else {
                $address->company->logo_url = null;
            }
        }

        return $addresses;
    }

    public function getMyAddresses(int $companyId): Collection
    {
        $addresses = Address::where('company_id', $companyId)
            ->with(['badges', 'rooms', 'rooms.photos', 'rooms', 'rooms.prices', 'company', 'operatingHours'])
            ->get();
        foreach ($addresses as $address) {
            if ($address->company->logo) {
                //TODO: переместить в модель? getLogoUrlAttribute => $this->logo ? Storage::disk('s3')->url($this->logo) : null;
                $address->company->logo_url = Storage::disk('s3')->url($address->company->logo);
            } else {
                $address->company->logo_url = null;
            }
        }
        return $addresses;
    }

    public function getAddressesByCompanySlug(string $slug): Collection
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        return Address::where('company_id', $company->id)
            ->with(['badges', 'rooms', 'rooms.photos', 'rooms', 'rooms.prices', 'company', 'operatingHours'])
            ->get();
    }

    public function getAddressBySlug(string $slug): Address
    {
        $address = Address::with([
            'badges',
            'rooms',
            'rooms.photos',
            'rooms.prices' => function ($query) {
                $query->where('is_enabled', true);
            },
            'company',
            'operatingHours',
            'equipments.type',
            'engineers' // Include engineers relationship
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        if ($address->company->logo) {
            $address->company->logo_url = Storage::disk('s3')->url($address->company->logo);
        } else {
            $address->company->logo_url = null;
        }

        return $address;
    }

    public function getAllStudios(): Collection
    {
        $addresses = Address::with(['badges', 'rooms', 'rooms.photos', 'rooms', 'rooms.prices', 'company', 'operatingHours'])->get();
        foreach ($addresses as $address) {
            if ($address->company->logo) {
                $address->company->logo_url = Storage::disk('s3')->url($address->company->logo);
            } else {
                $address->company->logo_url = null;
            }
        }

        return $addresses;
    }
}
