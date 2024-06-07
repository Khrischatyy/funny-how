<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CompanyRequest;
use App\Models\Address;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Services\CompanyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class CompanyController extends BaseController
{
    public function __construct(
        public CompanyRepository $companyRepository,
        public CompanyService $companyService,
    ) {}

    /**
     * @OA\Get(
     *     path="/company/{slug}",
     *     summary="Get company details",
     *     tags={"Company"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The slug of the company"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Company received successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Section"),
     *                 @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                 @OA\Property(property="slug", type="string", example="section"),
     *                 @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                 @OA\Property(property="rating", type="string", example="9.7"),
     *                 @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="updated_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="logo_url", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/https%3A//funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                 @OA\Property(property="addresses", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="latitude", type="string", example="37.609337"),
     *                         @OA\Property(property="longitude", type="string", example="55.758972"),
     *                         @OA\Property(property="street", type="string", example="Газетный переулок"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                         @OA\Property(property="city_id", type="integer", example=2),
     *                         @OA\Property(property="company_id", type="integer", example=1),
     *                         @OA\Property(property="badges", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="mixing"),
     *                                 @OA\Property(property="image", type="string", example="public/badges/mixing.svg"),
     *                                 @OA\Property(property="image_url", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/badges/mixing.svg"),
     *                                 @OA\Property(property="pivot", type="object",
     *                                     @OA\Property(property="address_id", type="integer", example=1),
     *                                     @OA\Property(property="badge_id", type="integer", example=1)
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Company received successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Company not found")
     * )
     */
    public function getCompany(string $slug): JsonResponse
    {
        try {
            $company = $this->companyService->getCompany($slug);

            return $this->sendResponse($company, 'Company received successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }
}
