<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * REGISTER
     */
    public function register(Request $request)
    {
        $groupId = $this->requestGroupId($request) ?? 'default';

        $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')->where(fn ($query) => $query->where('group_id', $groupId)),
            ],
            'password' => 'required|string|min:6',
            'name'     => 'nullable|string|max:255',
            'role'     => 'nullable|in:admin,host,user',
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name'     => $request->name ?? '',
            'role'     => $request->role ?? 'user',
            'group_id' => $groupId,
        ]);

        return response()->json([
            'code'   => 201,
            'status' => 'Created',
            'data'   => [
                'id'       => $user->id,
                'username' => $user->username,
                'name'     => $user->name,
                'role'     => $user->role,
            ],
        ], 201);
    }


    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $groupId = $this->requestGroupId($request);

        $user = User::query()
            ->when($groupId, fn ($query) => $query->forGroup($groupId))
            ->where('username', $request->username)
            ->first();

        if (!$user) {
            $user = User::where('username', $request->username)->first();

            if ($user) {
                $this->alignRequestGroupWithUser($request, $user);
                $groupId = $user->group_id;
            }
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'code'    => 401,
                'status'  => 'Unauthorized',
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Buat token baru
        $token = $user->generateToken();

        // Tandai aktif
        $user->markActive();

        $user->logDailyActivity();

        $this->alignRequestGroupWithUser($request, $user);

        return response()->json([
            'code'  => 200,
            'status'=> 'OK',
            'data'  => [
                'token'       => $token,
                'token_type'  => 'Bearer',
                'expires_at'  => optional($user->token_expires_at)->toDateTimeString(),
                'user'        => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'name'     => $user->name,
                    'role'     => $user->role,
                    'group_id' => $user->group_id,
                ]
            ]
        ]);
    }


    /**
     * CURRENT USER PROFILE
     */
    public function current(Request $request)
    {
        $user = $this->findUserFromRequest($request);

        if (!$user) {
            return response()->json([
                'code'    => 401,
                'status'  => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        // update last activity / tracking
        $user->markActive();
        $user->logDailyActivity();

        return response()->json([
            'code'  => 200,
            'status'=> 'OK',
            'data'  => [
                'id'       => $user->id,
                'username' => $user->username,
                'name'     => $user->name,
                'role'     => $user->role,
                'group_id' => $user->group_id,
                'token_expires_at' => optional($user->token_expires_at)->toDateTimeString(),
            ]
        ]);
    }


    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $user = $this->findUserFromRequest($request);

        if (!$user) {
            return response()->json([
                'code'    => 401,
                'status'  => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $user->clearToken();

        return response()->json([
            'code'   => 200,
            'status' => 'OK',
            'data'   => [
                'message' => 'Logged out'
            ]
        ]);
    }


    /**
     * ===== Helper Methods =====
     */

    private function extractToken(Request $request): ?string
    {
        $auth = $request->header('Authorization');

        if ($auth && Str::startsWith($auth, 'Bearer ')) {
            return Str::after($auth, 'Bearer ');
        }

        if ($request->has('token')) {
            return $request->query('token');
        }

        if ($request->hasHeader('X-Api-Token')) {
            return $request->header('X-Api-Token');
        }

        return null;
    }

    private function findUserFromRequest(Request $request): ?User
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

        if (!$user) return null;

        if (!$user->isTokenValid()) {
            $user->clearToken();
            return null;
        }

        return $user;
    }
}
