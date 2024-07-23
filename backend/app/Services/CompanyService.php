<?php

namespace App\Services;

use App\Http\Requests\BrandRequest;
use App\Http\Requests\CompanyRequest;
use App\Models\Address;
use App\Models\AdminCompany;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(public CompanyRepository $companyRepository, public ImageService $imageService)
    {}

    public function getCompany(string $slug)
    {
        return $this->companyRepository->getCompanyBySlug($slug);
    }

    public function getCompanyByAddressId(int $address_id)
    {
        return Address::find($address_id)->company;
    }

    public function createNewCompany(BrandRequest $companyRequest)
    {
        $user = Auth::user();

        if (AdminCompany::where('admin_id', $user->id)->exists()) {
            throw new Exception('User already has a company', 400);
        }

        $path = null;
        if ($companyRequest->hasFile('logo')) {
            $photo = $companyRequest->file('logo');
            $compressedImage = $this->imageService->toJpeg($photo);
            $path = 'company/images/' . uniqid() . '.jpg'; // Generate a unique path
            $url = $this->imageService->saveImageToStorage($compressedImage, $path);
        }

        $company = Company::create([
            'name' => $companyRequest->company,
            'logo' => $path,
            'slug' => Str::slug($companyRequest->company),
        ]);

        AdminCompany::create([
            'admin_id' => Auth::id(),
            'company_id' => $company->id,
        ]);

        return $company;
    }

    public function getCompanyById($companyId)
    {
        return Company::findOrFail($companyId);
    }
}
