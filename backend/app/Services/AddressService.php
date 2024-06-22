<?php

namespace App\Services;

use App\Exceptions\OperatingHourException;
use App\Http\Requests\AddressPhotosRequest;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\AdminCompany;
use App\Models\OperatingHour;
use App\Repositories\AddressRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AddressService
{
    public function __construct(public AddressRepository $addressRepository,
                                public BookingService $bookingService) {}
    public function createAddress(AddressRequest $addressRequest, $city, $company)
    {
        return Address::create([
            'street' => $addressRequest->street,
            'longitude' => $addressRequest->longitude,
            'latitude' => $addressRequest->latitude,
            'city_id' => $city->id,
            'company_id' => $company->id,
        ]);
    }

    public function getMyAddresses(): Collection
    {
        $user = Auth::user();
        $firstCompany = AdminCompany::where('admin_id', $user->id)->firstOrFail();

        if (Gate::denies('view', $firstCompany->company)) {
            abort(403, 'You are not authorized to view these addresses.');
        }

        $addresses = $this->addressRepository->getMyAddresses($firstCompany->company_id);

        $addressIds = $addresses->pluck('id');
        $operatingHours = $this->addressRepository->getOperatingHoursByAddressIds($addressIds);

        $addresses->each(function ($address) use ($operatingHours) {
            $address->working_hours = $operatingHours->get($address->id)->first();
        });

        return $addresses;
    }

    public function uploadPhotos(AddressPhotosRequest $request, Address $address)
    {
        if (Gate::denies('update', $address)) {
            abort(403, 'You are not authorized to update this address.');
        }

        Log::info('Starting photo upload process');

        if (!$request->hasFile('photos')) {
            Log::error('No photos found in request');
            return [];
        }

        $photos = $request->file('photos');

        $uploadedPhotos = [];

        foreach ($photos as $file) {
            Log::info('Uploading file: ' . $file->getClientOriginalName());

            $path = $file->store('photos', 's3');
            $index = $request->input('index');

            Log::info('File stored at: ' . $path);

            try {
                $photo = $address->photos()->create([
                    'path' => Storage::disk('s3')->url($path),
                    'index' => $index,
                ]);

                Log::info('Photo record created: ' . json_encode($photo));
                $uploadedPhotos[] = $photo;
            } catch (\Exception $e) {
                Log::error('Failed to create photo record for file: ' . $file->getClientOriginalName());
                Log::error('Error: ' . $e->getMessage());
            }
        }

        return $uploadedPhotos;
    }

    public function getAddressByCityIdWithWorkingHours(int $cityId): Collection
    {
        $addresses = $this->addressRepository->getAddressByCityId($cityId);

        $addressIds = $addresses->pluck('id');
        $operatingHours = $this->addressRepository->getOperatingHoursByAddressIds($addressIds);

        if ($operatingHours->isEmpty()) {
            throw new OperatingHourException("You didn't set hours", 400);
        }

        $addresses->each(function ($address) use ($operatingHours) {
            $address->working_hours = $operatingHours->get($address->id);
        });

        return $addresses;
    }
}
