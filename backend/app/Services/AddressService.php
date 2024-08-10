<?php

namespace App\Services;

use App\Exceptions\OperatingHourException;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\BrandRequest;
use App\Http\Requests\UpdatePhotoIndexRequest;
use App\Models\Address;
use App\Models\AdminCompany;
use App\Models\City;
use App\Models\Company;
use App\Models\FavoriteStudio;
use App\Models\User;
use App\Repositories\AddressRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AddressService
{
    public function __construct(public AddressRepository $addressRepository,
                                public ImageService $imageService) {}

    public function getAddressBySlug(string $addressSlug): Address
    {
        try {
            return $this->addressRepository->getAddressBySlug($addressSlug);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Address not found.");
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve address.', 500, $e);
        }
    }

    public function createAddress(AddressRequest|BrandRequest $request, City $city, Company $company): Address
    {
        $address = Address::create([
            'street' => $request->input('street'),
            'longitude' => $request->input('longitude'),
            'latitude' => $request->input('latitude'),
            'city_id' => $city->id,
            'company_id' => $company->id,
            'timezone' => $request->input('timezone'),
        ]);

        // Slug будет установлен автоматически при создании модели благодаря методу boot
        $address->save();

        return $address;
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

    public function listUserAddresses(User $user)
    {
        // Retrieve the company associated with the user
        $adminCompany = $user->adminCompany;

        if (!$adminCompany) {
            // Handle case where the user does not have an associated company
            throw new Exception('User does not have an associated company.');
        }

        $companyId = $adminCompany->company_id;

        // Retrieve the addresses associated with this company using the DB facade
        $addresses = DB::table('addresses')
            ->where('company_id', $companyId)
            ->select('id', 'street')
            ->get();

        return $addresses;
    }

    public function deleteAddress(int $address_id): void
    {
        $address = Address::findOrFail($address_id);

        if (Gate::denies('delete', $address)) {
            abort(403, 'You are not authorized to delete this address.');
        }

        $address->operatingHours()->delete();

        //TODO if we don't need any info about previous prices, badges, photos (maybe even bookings
        //then i can delete them too, now left it.

        $address->delete();
    }

    public function getAllStudios(): Collection
    {
        return $this->addressRepository->getAllStudios();
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

    public function toggleFavorite(int $userId, int $addressId)
    {
        $favoriteStudio = FavoriteStudio::where('user_id', $userId)->where('address_id', $addressId)->first();

        if ($favoriteStudio) {
            $favoriteStudio->delete();
            return ['is_favorite' => false];
        } else {
            FavoriteStudio::create([
                'user_id' => $userId,
                'address_id' => $addressId,
            ]);
            return ['is_favorite' => true];
        }
    }

    public function updateSlug(Address $address, string $newSlug): Address
    {
        try {
            $address->slug = $newSlug;
            $address->save();

            return $address;
        } catch (Exception $e) {
            throw new Exception('Failed to update slug.', 500, $e);
        }
    }

    public function filterMyAddresses(int $companyId, array $filters): Collection
    {
        $query = Address::where('company_id', $companyId)
            ->with(['badges', 'rooms', 'rooms.photos', 'rooms.prices', 'company', 'operatingHours']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('company', function ($q2) use ($filters) {
                    $q2->where('name', 'like', '%' . $filters['search'] . '%');
                })->orWhere('street', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['city'])) {
            $query->where('city_id', $filters['city']);
        }

        if (!empty($filters['price'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('prices', function ($q2) use ($filters) {
                    $q2->where('total_price', '<=', $filters['price']);
                });
            });
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', '>=', $filters['rating']);
        }

        return $query->with(['city', 'company'])->get();
    }
}
