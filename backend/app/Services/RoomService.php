<?php

namespace App\Services;

use App\Exceptions\OperatingHourException;
use App\Http\Requests\AddressPhotosRequest;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\BrandRequest;
use App\Http\Requests\RoomPhotosRequest;
use App\Http\Requests\RoomRequest;
use App\Http\Requests\UpdatePhotoIndexRequest;
use App\Models\Address;
use App\Models\AddressPhoto;
use App\Models\AdminCompany;
use App\Models\City;
use App\Models\Company;
use App\Models\FavoriteStudio;
use App\Models\Room;
use App\Models\RoomPhoto;
use App\Models\User;
use App\Repositories\AddressRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class RoomService
{
    public function __construct(public AddressRepository $addressRepository,
                                public ImageService      $imageService)
    {
    }

    public function createRoom(String $name, Address $address): Room
    {
        $room = Room::create([
            'name' => $name,
            'address_id' => $address->id,
        ]);

        return $room;
    }

    public function uploadAddressPhotos(RoomPhotosRequest $request, int $room_id): array
    {
        $room = Room::with('photos')->findOrFail($room_id);

        if (!$request->hasFile('photos')) {
            throw new \Exception('No photos uploaded.');
        }

        $photos = $this->uploadPhotos($request, $room);

        if (empty($photos)) {
            throw new \Exception('No photos were saved.');
        }

        // Обновляем room с жадной загрузкой фотографий после загрузки
        $room = Room::with('photos')->findOrFail($room_id);

//        dd($address->photos);

        return [
            'photos' => $room->photos,
            'message' => 'Photos uploaded successfully.'
        ];
    }

    public function uploadPhotos(RoomPhotosRequest $request, Room $room)
    {
        Log::info('Starting photo upload process');

        $photos = $request->file('photos');
        $uploadedPhotos = [];

        // Получаем текущее максимальное значение index для данного адреса
        $currentMaxIndex = $room->photos()->max('index') ?? 0;

        foreach ($photos as $file) {
            Log::info('Uploading file: ' . $file->getClientOriginalName());


            // Compress, maybe resize, and optimize image in memory
            $compressedImage = $this->imageService->toJpeg($file);
            $optimizedImage = $this->imageService->optimizeImageInMemory($compressedImage);

            // Генерируем путь для сохранения на S3
            $photoName = uniqid() . '.jpg';
            $path = 'studio/photos/' . $photoName;
            $index = ++$currentMaxIndex;  // Увеличиваем индекс для каждой новой фотографии

            // Сохраняем изображение на S3 и получаем URL
            $photoUrl = $this->imageService->saveImageToStorage($optimizedImage, $path);


            try {
                $photo = $room->photos()->create([
                    'path' => $photoUrl,
                    'index' => $index,
                ]);

                Log::info('Photo record created: ' . json_encode($photo));
                $uploadedPhotos[] = $photo;
            } catch (Exception $e) {
                Log::error('Failed to create photo record for file: ' . $file->getClientOriginalName());
                Log::error('Error: ' . $e->getMessage());
            }
        }

        return $uploadedPhotos;
    }

    public function updatePhotoIndex(UpdatePhotoIndexRequest $request, int $photo_id): array
    {
        $photo = RoomPhoto::findOrFail($photo_id);
        $newIndex = $request->input('index');
        $roomId = $photo->room_id;
        $oldIndex = $photo->index;

        Log::info('Updating photo index', ['photo_id' => $photo_id, 'current_index' => $oldIndex, 'new_index' => $newIndex]);

        DB::beginTransaction();

        try {
            // Check if the new index already exists for the address_id
            $existingPhoto = RoomPhoto::where('room_id', $roomId)->where('index', $newIndex)->first();

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

    public function updateName(Room $room, string $newName):Room
    {
        try {
            $room->name = $newName;
            $room->save();

            return $room;
        } catch (Exception $e) {
            throw new Exception('Failed to update name.', 500, $e);
        }
    }

}
