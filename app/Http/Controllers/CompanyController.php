<?php
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index(Request $request) {
        return Company::allCompanies($request->search)->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE)->values();
    }

    public function store(Request $request) {
        $company = Company::createCompany($request);

        if ($company instanceof Response) {
            return $company;
        }

        return response()->json([
            'success' => true,
            'message' => 'Company added successfully',
            'data' => $company
        ], 201);
    }

    public function update(Request $request, $id) {
        $company = Company::updateCompany($request, $id);

        if ($company instanceof Response) {
            return $company;
        }

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $company
        ]);
    }

    public function destroy($id) {
        $company = Company::deleteCompany($id);

        if ($company instanceof Response) {
            return $company;
        }

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully'
        ]);
    }
}
