<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
        try {
            // Check if the authenticated user is a super_admin (role_id 1)
            $authenticatedUserRole = auth()->user()->roles->first();
            if ($authenticatedUserRole->id !== 1) {
                return $this->error_response2('Unauthorized. You do not have the required role to create a company.');
            }

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string',
                'company_phone' => 'required|string|unique:users,phone',
                'company_inn' => 'string',
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }
            $data = $request->only('company_name', 'company_inn');

            $data['company_phone'] = preg_replace('/[^0-9]/', '', $request->get('company_phone'));

            $company = Company::create($data);

            $message = [
                'uz' => 'Kompaniya yaratildi',
                'ru' => 'Компания создана',
                'en' => 'Company created',
            ];

            return $this->success_response($company, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function viewCompanyUsers(Request $request)
    {
        try {
            // Check if the authenticated user is a company_admin (role_id 2)
            $authenticatedUser = auth()->user();
            $authenticatedUserRole = $authenticatedUser->roles->first();

            if ($authenticatedUserRole->id !== 3) {
                return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
            }

            // Retrieve the company information from the user's token
            $company = $authenticatedUser->company;

            if (!$company) {
                return $this->error_response2('Company not found');
            }

            // Get the list of users belonging to the company
            $users = User::where('company_id', $company->id)->get();

            return $this->success_response($users);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function changeCompanyStatus(Request $request, $companyId)
    {
        try {
            // Check if the authenticated user is a super_admin (role_id 1)
            $authenticatedUserRole = auth()->user()->roles->first();
            if ($authenticatedUserRole->id !== 1) {
                return $this->error_response2('Unauthorized. You do not have the required role to change company status.');
            }

            // Find the company by ID
            $company = Company::find($companyId);

            if (!$company) {
                return $this->error_response2('Company not found');
            }

            $newStatus = $request->input('status');

            if (!in_array($newStatus, ['active', 'inactive'])) {
                return $this->error_response2('Invalid company status');
            }

            $company->update(['status' => $newStatus]);

            $message = [
                'uz' => 'Kompaniya holati o\'zgartirildi',
                'ru' => 'Статус компании изменен',
                'en' => 'Company status changed',
            ];

            // Update user statuses associated with this company
            User::where('company_id', $company->id)->update(['status' => $newStatus]);

            return $this->success_response($company, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
