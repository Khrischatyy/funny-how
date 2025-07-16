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
            ->with([
                'badges',
                'rooms',
                'rooms.photos',
                'rooms.prices',
                'company',
                'company.adminCompany.user',
                'operatingHours'
            ])
            ->get()
            ->filter(function ($address) {
                return $address->is_complete;
            })
            ->each(function ($address) {
                $address->company->logo_url = $address->company->logo
                    ? Storage::disk('s3')->url($address->company->logo)
                    : null;
                
                // Add user_id to company object
                if ($address->company->adminCompany && $address->company->adminCompany->user) {
                    $address->company->user_id = $address->company->adminCompany->user->id;
                }
            })
            ->values();

        return $addresses;
    }

    public function getMyAddresses(int $companyId): Collection
    {
        $addresses = Address::where('company_id', $companyId)
            ->with([
                'badges',
                'rooms',
                'rooms.photos',
                'rooms',
                'rooms.prices',
                'company',
                'company.adminCompany.user',
                'operatingHours'
            ])
            ->get();
        foreach ($addresses as $address) {
            if ($address->company->logo) {
                //TODO: переместить в модель? getLogoUrlAttribute => $this->logo ? Storage::disk('s3')->url($this->logo) : null;
                $address->company->logo_url = Storage::disk('s3')->url($address->company->logo);
            } else {
                $address->company->logo_url = null;
            }

            // Add user_id to company object
            if ($address->company->adminCompany && $address->company->adminCompany->user) {
                $address->company->user_id = $address->company->adminCompany->user->id;
            }
        }
        return $addresses;
    }

    public function getAddressesByCompanySlug(string $slug): Collection
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $addresses = Address::where('company_id', $company->id)
            ->with([
                'badges',
                'rooms',
                'rooms.photos',
                'rooms',
                'rooms.prices',
                'company',
                'company.adminCompany.user',
                'operatingHours'
            ])
            ->get();

        // Add user_id to company object for each address
        foreach ($addresses as $address) {
            if ($address->company->adminCompany && $address->company->adminCompany->user) {
                $address->company->user_id = $address->company->adminCompany->user->id;
            }
        }

        return $addresses;
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
            'company.adminCompany.user',
            'operatingHours',
            'equipments.type',
            'engineers.engineerRate'
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        if ($address->company->logo) {
            $address->company->logo_url = Storage::disk('s3')->url($address->company->logo);
        } else {
            $address->company->logo_url = null;
        }

        // Add user_id to company object
        if ($address->company->adminCompany && $address->company->adminCompany->user) {
            $address->company->user_id = $address->company->adminCompany->user->id;
        }

        return $address;
    }

    public function getAllStudios(): Collection
    {
        $addresses = Address::with([
            'badges',
            'rooms',
            'rooms.photos',
            'rooms',
            'rooms.prices',
            'company',
            'company.adminCompany.user',
            'operatingHours'
        ])->get();

        foreach ($addresses as $address) {
            if ($address->company->logo) {
                $address->company->logo_url = Storage::disk('s3')->url($address->company->logo);
            } else {
                $address->company->logo_url = null;
            }

            // Add user_id to company object
            if ($address->company->adminCompany && $address->company->adminCompany->user) {
                $address->company->user_id = $address->company->adminCompany->user->id;
            }
        }

        return $addresses;
    }

    public function getCitiesByCompany(int $companyId)
    {
        return Address::where('company_id', $companyId)
            ->with('city:id,name')
            ->get()
            ->pluck('city')
            ->unique('id')
            ->values();
    }

    public function getRandomStudio(): Address
    {
        $address = Address::
        whereHas('company.adminCompany.user', function ($query) {
            $query->whereNotNull('stripe_id')->orWhere('payment_gateway', 'square');
        })->whereHas('operatingHours')->with(['badges', 'rooms', 'rooms.photos', 'rooms', 'rooms.prices', 'company', 'operatingHours'])
        ->inRandomOrder()->first();

        return $address;
    }
}
