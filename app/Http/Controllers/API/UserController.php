<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'username' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'username')
                    ->ignore($user->id)
                    ->where(fn ($query) => $query->where('group_id', $user->group_id)),
            ],
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

        $groupId = $this->requestGroupId($request) ?? 'default';

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => User::forGroup($groupId)
                ->select('id','username','name','role','last_active_at')
                ->get()
        ]);
    }


    /**
     * POST: /api/user
     * Admin only: create a new user with optional role & group assignment
     */
    public function store(Request $request)
    {
        $currentUser = $this->findUser($request);

        if (!$currentUser || $currentUser->role !== 'admin') {
            return response()->json([
                'code' => 403,
                'status' => 'Forbidden',
                'message' => 'Only admin can access'
            ], 403);
        }

        $defaultGroupId = $this->requestGroupId($request) ?? 'default';
        $requestedGroupId = $request->filled('group_id') ? $request->input('group_id') : null;
        $targetGroupId = $requestedGroupId ?? $defaultGroupId;

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')
                    ->where(fn ($query) => $query->where('group_id', $targetGroupId)),
            ],
            'password' => 'required|string|min:6',
            'name'     => 'nullable|string|max:255',
            'role'     => 'nullable|in:admin,host,user',
            'group_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('host_groups', 'group_id'),
            ],
        ]);

        $groupId = $validated['group_id'] ?? $targetGroupId;

        $newUser = User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'name'     => $validated['name'] ?? '',
            'role'     => $validated['role'] ?? 'user',
            'group_id' => $groupId,
        ]);

        return response()->json([
            'code' => 201,
            'status' => 'Created',
            'data' => [
                'id'       => $newUser->id,
                'username' => $newUser->username,
                'name'     => $newUser->name,
                'role'     => $newUser->role,
                'group_id' => $newUser->group_id,
            ]
        ], 201);
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

        $groupId = $this->requestGroupId($request);

        $query = User::where('token', $token);

        if ($groupId) {
            $query->forGroup($groupId);
        }

        $user = $query->first();

        if (!$user) {
            $user = User::where('token', $token)->first();

            if ($user) {
                $this->alignRequestGroupWithUser($request, $user);
            }
        }

        if (!$user || !$user->isTokenValid()) return null;

        $this->alignRequestGroupWithUser($request, $user);

        return $user;
    }
    // End of class

}
