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

    public function index() {
        return Company::all();
    }

    public function store(Request $request) {
        $data = $request->only('name');
        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $company = Company::create($request->all());
        $company->save();

        return response()->json([
            'success' => true,
            'message' => 'Company added successfully',
            'data' => $company
        ], 201);
    }

    public function show($id) {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, company not found.'
            ], 404);
        }

        return $company;
    }

    public function update(Request $request, $id) {
        $data = $request->only('name');
        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $company = Company::findOrFail($id)->fill($request->all());
        $company->save();

        return response()->json([
            'success' => true,
            'message' => 'Gear updated successfully',
            'data' => $company
        ]);
    }

    public function destroy($id) {
        Company::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully'
        ]);
    }

    public function rules() {
        return [
            'name' => 'required|string'
        ];
    }
}