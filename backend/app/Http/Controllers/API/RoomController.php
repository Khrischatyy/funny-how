<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RoomPhotosRequest;
use App\Http\Requests\RoomPriceDeleteRequest;
use App\Http\Requests\RoomPricesRequest;
use App\Http\Requests\BrandRequest;
use App\Http\Requests\RoomRequest;
use App\Http\Requests\UpdateNameRequest;
use App\Http\Requests\UpdatePhotoIndexRequest;
use App\Models\Address;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Repositories\AddressRepository;
use App\Services\RoomService;
use App\Services\CityService;
use App\Services\CompanyService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoomController extends BaseController
{
    public function __construct(public AddressRepository $addressRepository,
                                public RoomService $roomService,
                                public CityService $cityService,
                                public CompanyService $companyService)
    {}

    public function uploadPhotos(RoomPhotosRequest $request): JsonResponse
    {
        $room_id = (int) $request->input('room_id');

        try {
            $result = $this->roomService->uploadAddressPhotos($request, $room_id);

            return $this->sendResponse($result['photos'], 'Photos uploaded successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to upload photos.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function createRoom(RoomRequest $request): JsonResponse
    {
        try {
            $address = Address::findOrFail($request->input('address_id'));
            $room = $this->roomService->createRoom($request->input('name'), $address);

            return $this->sendResponse($room, 'Room created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create room.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getPrices($room_id): JsonResponse
    {
        try {
            $room = Room::with('prices')->findOrFail($room_id);
            return $this->sendResponse($room->prices, 'Room prices retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Room not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve room prices.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function createOrUpdatePrice(RoomPricesRequest $request, int $room_id): JsonResponse
    {
        $hours = $request->input('hours');
        $total_price = $request->input('total_price');
        $is_enabled = $request->input('is_enabled');

        $room_price_id = $request->input('id');

        try {
            $room = Room::findOrFail($room_id);

            // Calculate price per hour
            $price_per_hour = $total_price / $hours;

            if ($room_price_id) {
                // Find the existing price entry
                $price_entry = $room->prices()->findOrFail($room_price_id);

                // Update the existing price entry
                $price_entry->update([
                    'hours' => $hours,
                    'total_price' => $total_price,
                    'price_per_hour' => $price_per_hour,
                    'is_enabled' => $is_enabled,
                ]);
            } else {
                // Create the new price entry
                $room->prices()->create([
                    'address_id' => $room_id,
                    'hours' => $hours,
                    'total_price' => $total_price,
                    'price_per_hour' => $price_per_hour,
                    'is_enabled' => $is_enabled,
                ]);
            }

            return $this->sendResponse($room->prices, 'Room prices updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address or price entry not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update studio prices.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function deletePrices(RoomPriceDeleteRequest $request): JsonResponse
    {
        $room_id = $request->input('room_id');
        $room_price_id = $request->input('room_price_id');


        try {
            $price = RoomPrice::where('room_id', $room_id)->where('id', $room_price_id)->firstOrFail();
            Log::info('price', [$price]);
            $price->delete();

            // Fetch the updated list of prices for the address
            $prices = Room::with('prices')->findOrFail($room_id)->prices;

            return $this->sendResponse($prices, 'Room price deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Room or RoomPrice not found', 500);
        } catch (Exception $e) {
            return $this->sendError('Failed to delete room price.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updatePhotoIndex(UpdatePhotoIndexRequest $request): JsonResponse
    {
        $photo_id = $request->input('room_photo_id');

        try {
            $result = $this->roomService->updatePhotoIndex($request, $photo_id);
            return $this->sendResponse($result, 'Photo index updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Photo not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update photo index.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updateName(UpdateNameRequest $request, Room $room): JsonResponse
    {
        try {
            $updatedRoom = $this->roomService->updateName($room, $request->input('new_name'));

            return $this->sendResponse($updatedRoom, 'Name updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Room not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update slug.', 500, ['error' => $e->getMessage()]);
        }
    }
}
