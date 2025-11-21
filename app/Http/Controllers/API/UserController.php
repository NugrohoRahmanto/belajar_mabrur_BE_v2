<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET: /api/user/me
     * Ambil data user berdasarkan token
     */
    public function me(Request $request)
    {
        $user = $this->findUser($request);

        if (!$user) {
            return response()->json([
                'code' => 401,
                'status' => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $user->markActive();
        $user->logDailyActivity();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $user
        ]);
    }


    /**
     * PUT: /api/user/update
     * Update profile (name & username)
     */
    public function updateProfile(Request $request)
    {
        $user = $this->findUser($request);

        if (!$user) {
            return response()->json([
                'code' => 401,
                'status' => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $request->validate([
            'name'     => 'nullable|string|max:255',
            'username' => 'nullable|string|max:100|unique:users,username,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'username' => $request->username ?? $user->username,
        ]);

        $user->logDailyActivity();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $user
        ]);
    }


    /**
     * PUT: /api/user/password
     * Ganti password
     */
    public function updatePassword(Request $request)
    {
        $user = $this->findUser($request);

        if (!$user) {
            return response()->json([
                'code' => 401,
                'status' => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'code' => 401,
                'status' => 'Unauthorized',
                'message' => 'Old password incorrect'
            ], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $user->logDailyActivity();
        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'message' => 'Password updated'
        ]);
    }

    /**
     * GET: /api/user/all
     * Hanya untuk admin melihat semua user
     */
    public function allUsers(Request $request)
    {
        $user = $this->findUser($request);

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'code' => 403,
                'status' => 'Forbidden',
                'message' => 'Only admin can access'
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => User::select('id','username','name','role','last_active_at')->get()
        ]);
    }


    /**
     * ===== Helper =====
     */

    private function extractToken(Request $request): ?string
    {
        $auth = $request->header('Authorization');

        if ($auth && str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }

        if ($request->has('token')) {
            return $request->query('token');
        }

        if ($request->hasHeader('X-Api-Token')) {
            return $request->header('X-Api-Token');
        }

        return null;
    }

    private function findUser(Request $request): ?User
    {
        $token = $this->extractToken($request);

        if (!$token) return null;

        $user = User::where('token', $token)->first();

        if (!$user || !$user->isTokenValid()) return null;

        return $user;
    }
    // End of class

}
