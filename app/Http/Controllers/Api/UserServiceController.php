<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class UserServiceController extends Controller
{
    /**
     * Kullanıcı oluştur ve hizmet talebi başlat
     */
    public function createUserWithService(Request $request)
    {
        // Gelen verileri doğrula
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'required|string|max:20',
            'name' => 'string|max:255',
            'service_description' => 'string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Veritabanı işlemlerini transaction içinde yap
            DB::beginTransaction();

            // Kullanıcı oluştur
            $user = User::create([
                'name' => $request->name ?? 'Müşteri',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
            ]);

            // Hizmet talebi oluştur
            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'service_type' => 'online_service',
                'status' => 'pending',
                'description' => $request->service_description ?? 'Online hizmet talebi',
                'additional_data' => [
                    'created_via_api' => true,
                    'phone' => $request->phone,
                    'created_at_formatted' => now()->format('d.m.Y H:i')
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kullanıcı ve hizmet talebi başarıyla oluşturuldu',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'created_at' => $user->created_at->format('d.m.Y H:i')
                    ],
                    'service_request' => [
                        'id' => $serviceRequest->id,
                        'service_type' => $serviceRequest->service_type,
                        'status' => $serviceRequest->status,
                        'description' => $serviceRequest->description,
                        'created_at' => $serviceRequest->created_at->format('d.m.Y H:i')
                    ]
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Kullanıcı oluşturulurken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kullanıcının hizmet taleplerini listele
     */
    public function getUserServiceRequests($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $serviceRequests = $user->serviceRequests()->latest()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone
                    ],
                    'service_requests' => $serviceRequests->map(function ($request) {
                        return [
                            'id' => $request->id,
                            'service_type' => $request->service_type,
                            'status' => $request->status,
                            'description' => $request->description,
                            'created_at' => $request->created_at->format('d.m.Y H:i')
                        ];
                    })
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kullanıcı bulunamadı',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
