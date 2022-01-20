<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public static function allCompanies($search) {
        $companies = Company::where('name', 'ilike', "%$search%")->get();
        foreach ($companies as $company) {
            $company['user_count'] = $company->users()->count();
        }

        return $companies;
    }

    public static function createCompany($request) {
        if (!!$validation = self::validateFields($request)) {
            return $validation;
        }

        $company = Company::create($request->all());
        $company->save();

        return $company;
    }

    public static function updateCompany($request, $id) {
        if (!!$validation = self::validateFields($request)) {
            return $validation;
        }

        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, company not found.'
            ], 404);
        }

        $company->fill($request->all())->save();

        return $company;
    }

    public static function deleteCompany($id) {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, company not found.'
            ], 404);
        }

        if(!$company->users()->get()->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Company still has users.'
            ], 400);
        }

        $company->delete();
    }

    private static function validateFields($request) {
        $data = $request->only('name');
        $validator = Validator::make($data, ['name' => 'required|string|unique:companies']);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}
