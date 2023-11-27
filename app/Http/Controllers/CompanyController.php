<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Position;
use App\Models\Rank;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
        // Check if the authenticated user is a super_admin (role_id 1)
        if (auth()->user()->roles->first()->id !== 1) {
            return $this->error_response2('Unauthorized. You do not have the required role to create a company.');
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string',
            'company_phone' => 'required|string|unique:users,phone',
            'company_inn' => 'required|string|unique:companies,company_inn',
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->all());
        }

        $data = $request->only('company_name', 'company_inn');
        $data['company_phone'] = preg_replace('/[^0-9]/', '', $request->get('company_phone'));

        $company = Company::create($data);

        $adminPosition = Position::create([
            'position' => 'Admin',
            'company_id' => $company->id,
        ]);

        $adminRank = Rank::create([
            'rank' => 'Admin',
            'company_id' => $company->id,
        ]);

        $message = [
            'uz' => 'Kompaniya yaratildi',
            'ru' => 'Компания создана',
            'en' => 'Company created',
        ];

        return $this->success_response($company, $message);
    }


//    public function viewCompanyUsers(Request $request)
//    {
//
//        // Check if the authenticated user has one of the allowed role IDs (3, 4, or 5)
//        $authenticatedUser = auth()->user();
//        $authenticatedUserRole = $authenticatedUser->roles->first();
//
//        $allowedRoleIds = [1, 3, 4, 5];
//
//        if (!in_array($authenticatedUserRole->id, $allowedRoleIds)) {
//            return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
//        }
//
//        // Retrieve the company information from the user's token
//        $company = $authenticatedUser->company;
//
//        if (!$company) {
//            return $this->error_response2('Company not found');
//        }
//
//        // Get the list of users belonging to the company
//        $users = User::where('company_id', $company->id)->get();
//
//        return $this->success_response($users);
//
//    }

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


            User::where('company_id', $company->id)->update(['status' => $newStatus]);

            return $this->success_response($company, $message);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function companyList()
    {
        try {
            $authenticatedUserRole = auth()->user()->roles->first();
            if ($authenticatedUserRole->id !== 1) {
                return $this->error_response2('Unauthorized. You do not have the required role to change company status.');
            }
            $companies = Company::all();

            $message = [
                'uz' => 'Barcha kompaniyalar ro`yxati',
                'ru' => 'Список всех компаний',
                'en' => 'List of all companies',
            ];

            return $this->success_response($companies, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserInfoAndTruckInfo(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $companyId = auth()->user()->company_id;

        $users = User::with(['tracks' => function ($query) use ($date) {
            $query->whereBetween("created_at", [$date, $date . " 23:59:59"])
                ->orderBy('created_at', 'asc'); // Order the tracks by creation date in ascending order
        }])
            ->where('company_id', $companyId)
            ->get();

        $result = [];

        foreach ($users as $user) {
            $firstTrackType0 = null;
            $lastTrackType1 = null;

            foreach ($user->tracks as $track) {
                if ($track->type === 0 && $firstTrackType0 === null) {
                    $firstTrackType0 = $track;
                }
                if ($track->type === 1) {
                    $lastTrackType1 = $track;
                }
            }

            $result[] = [
                'user' => $user,
                'first_track_type_0' => $firstTrackType0,
                'last_track_type_1' => $lastTrackType1,
            ];
        }
        $message = [
            'uz' => 'Muvaffaqqiyatli',
            'ru' => 'Успешно',
            'en' => 'Successful',
        ];
        return $this->success_response($result, $message);
    }


    public function getCompanyAdmins(Request $request, $companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return $this->error_response2('Company not found.');
        }

        $admins = User::where('company_id', $company->id)
            ->whereHas('roles', function ($query) {
                $query->where('role_id', 3);
            })
            ->get();

        $adminsWithLastTrack = [];

        foreach ($admins as $admin) {
            $lastTrack = Track::where('user_id', $admin->id)->latest()->first();

            if ($lastTrack) {
                $admin->last_track_image = asset($lastTrack->image);
            } else {
                $admin->last_track_image = null;
            }

            $adminsWithLastTrack[] = $admin;
        }

        $message = [
            'uz' => 'Kompaniya administratorlari muvaffaqiyatli topildi',
            'ru' => 'Администраторы компании успешно найдены',
            'en' => 'Company admins are successfully found',
        ];

        return $this->success_response($adminsWithLastTrack, $message);
    }


    public function getCompanyHrs(Request $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->error_response2('Company not found.');
        }

        $hrs = User::where('company_id', $company->id)
            ->whereHas('roles', function ($query) {
                $query->whereIn('role_id', [4, 5]);
            })
            ->with('roles')
            ->get();

        $hrsWithLastTrack = [];

        foreach ($hrs as $hr) {
            $lastTrack = Track::where('user_id', $hr->id)->latest()->first();

            if ($lastTrack) {
                $hr->last_track_image = asset($lastTrack->image);
            } else {
                $hr->last_track_image = null;
            }

            $hrsWithLastTrack[] = $hr;
        }

        $message = [
            'uz' => 'Kompaniya HRlar muvaffaqiyatli topildi',
            'ru' => 'HR компании успешно найдены',
            'en' => 'Company HRs are successfully found',
        ];

        return $this->success_response($hrsWithLastTrack, $message);
    }

// ====================  test ====================
    public function viewCompanyUsers(Request $request)
    {
        // Check if the authenticated user has one of the allowed role IDs (3, 4, or 5)
        $authenticatedUser = auth()->user();
        $authenticatedUserRole = $authenticatedUser->roles->first();

        $allowedRoleIds = [1, 3, 4, 5];

        if (!in_array($authenticatedUserRole->id, $allowedRoleIds)) {
            return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
        }

        // Retrieve the company information from the user's token
        $company = $authenticatedUser->company;

        if (!$company) {
            return $this->error_response2('Company not found');
        }

        // Get the list of users belonging to the company
        $users = User::where('company_id', $company->id)->get();

        $usersWithLastTrack = [];

        foreach ($users as $user) {
            $lastTrack = Track::where('user_id', $user->id)->latest()->first();

            if ($lastTrack) {
                // Add image information to the user data
                $user->last_track_image = asset($lastTrack->image);
            } else {
                // Set a default image URL or null if no track is found
                $user->last_track_image = null;
            }

            $usersWithLastTrack[] = $user;
        }

        return $this->success_response($usersWithLastTrack);
    }


}
