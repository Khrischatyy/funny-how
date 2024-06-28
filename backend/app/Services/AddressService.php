<?php

namespace App\Services;

use App\Exceptions\OperatingHourException;
use App\Http\Requests\AddressPhotosRequest;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\UpdatePhotoIndexRequest;
use App\Models\Address;
use App\Models\AddressPhoto;
use App\Models\AdminCompany;
use App\Models\OperatingHour;
use App\Repositories\AddressRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AddressService
{
    public function __construct(public AddressRepository $addressRepository,
                                public BookingService $bookingService) {}

    public function getAddressById(int $addressId): Address
    {
        try {
            return $this->addressRepository->getAddressById($addressId);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Address not found.");
        }
    }

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

        $addresses->each(function ($address) {
            $address->company_slug = $address->company->slug;
        });

        return $addresses;
    }

    public function getAddressByCityIdWithWorkingHours(int $cityId): Collection
    {
        $addresses = $this->addressRepository->getAddressByCityId($cityId);

        if ($addresses->isEmpty()) {
            throw new OperatingHourException("No addresses found for the given city ID.", 400);
        }

        return $addresses;
    }

    public function getAddressesByCompanySlug(string $slug): Collection
    {
        try {
            return $this->addressRepository->getAddressesByCompanySlug($slug);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve addresses', 500, $e);
        }
    }

    public function uploadAddressPhotos(AddressPhotosRequest $request, int $address_id): array
    {
        $address = Address::with('photos')->findOrFail($address_id);

        if (Gate::denies('update', $address)) {
            abort(403, 'You are not authorized to update this address.');
        }

        if (!$request->hasFile('photos')) {
            throw new \Exception('No photos uploaded.');
        }

        $photos = $this->uploadPhotos($request, $address);

        if (empty($photos)) {
            throw new \Exception('No photos were saved.');
        }

        // Обновляем адрес с жадной загрузкой фотографий после загрузки
        $address = Address::with('photos')->findOrFail($address_id);

        return [
            'photos' => $address->photos,
            'message' => 'Photos uploaded successfully.'
        ];
    }

    public function uploadPhotos(AddressPhotosRequest $request, Address $address)
    {
        Log::info('Starting photo upload process');

        $photos = $request->file('photos');
        $uploadedPhotos = [];

        // Получаем текущее максимальное значение index для данного адреса
        $currentMaxIndex = $address->photos()->max('index') ?? 0;

        foreach ($photos as $file) {
            Log::info('Uploading file: ' . $file->getClientOriginalName());

            $path = $file->store('photos', 's3');
            $index = ++$currentMaxIndex;  // Увеличиваем индекс для каждой новой фотографии

            Log::info('File stored at: ' . $path);

            try {
                $photo = $address->photos()->create([
                    'path' => $path,
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

    public function updatePhotoIndex(UpdatePhotoIndexRequest $request, int $photo_id): array
    {
        $photo = AddressPhoto::findOrFail($photo_id);
        $newIndex = $request->input('index');
        $addressId = $photo->address_id;
        $oldIndex = $photo->index;

        Log::info('Updating photo index', ['photo_id' => $photo_id, 'current_index' => $oldIndex, 'new_index' => $newIndex]);

        DB::beginTransaction();

        try {
            // Check if the new index already exists for the address_id
            $existingPhoto = AddressPhoto::where('address_id', $addressId)->where('index', $newIndex)->first();

            if ($existingPhoto) {
                // Use a temporary index to avoid unique constraint violation
                $tempIndex = $newIndex + 1000; // Ensure this temp index doesn't conflict

                // Assign temporary index to the existing photo
                $existingPhoto->update(['index' => $tempIndex]);

                // Update the photo to use the new index
                $photo->update(['index' => $newIndex]);

                // Update the existing photo to use the old index of the photo
                $existingPhoto->update(['index' => $oldIndex]);
            } else {
                // If no conflict, simply update the index
                $photo->update(['index' => $newIndex]);
            }

            DB::commit();

            return $photo->toArray();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update photo index', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getAllStudios(): Collection
    {
        return $this->addressRepository->getAllStudios();
    }


}
