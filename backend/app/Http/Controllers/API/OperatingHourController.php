<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\OperatingHourRequest;
use App\Services\CompanyService;
use App\Services\OperatingHourService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OperatingHourController extends BaseController
{
    public function __construct(public OperatingHourService $operatingHourService, public CompanyService $companyService)
    {}

    public function getOperationModes(): JsonResponse
    {
        $operationModes = DB::table('operating_modes')->get();

        return $this->sendResponse($operationModes, 'Operation modes received');
    }

    public function setOperatingHours(OperatingHourRequest $operatingHourRequest): JsonResponse
    {
        $address_id = $operatingHourRequest->input('address_id');
        $mode = (int) $operatingHourRequest->input('mode_id');
        $open_time = $operatingHourRequest->input('open_time');
        $close_time = $operatingHourRequest->input('close_time');

        $company = $this->companyService->getCompanyByAddressId($address_id);

        //проверка может ли studio_owner апдейтить студию
        $this->authorize('update', $company);


        $open_time_weekend = $operatingHourRequest->input('open_time_weekend', null);
        $close_time_weekend = $operatingHourRequest->input('close_time_weekend', null);

        $hours = match ($mode) {
            1 => $this->operatingHourService->permanent($address_id, $mode),
            2 => $this->operatingHourService->everyday($address_id, $mode, $open_time, $close_time),
            3 => $this->operatingHourService->regular($address_id, $open_time, $close_time, $open_time_weekend, $close_time_weekend),
//            4 => $this->operatingHourService->specificDay(),
        };

        return $this->sendResponse($hours, 'Hours were added');
    }
}
