<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

/**
 * Token auth for the mobile app. Tokens are RS256-signed by the auth service;
 * older app builds still send HS256 tokens signed with the shared key.
 */
class MobileAuthController extends Controller
{
    public function bookings(Request $request)
    {
        $user = $this->userFromToken((string) $request->bearerToken());

        return Booking::where('user_id', $user->id)->latest()->get();
    }

    private function userFromToken(string $token): User
    {
        $key = file_get_contents(config('services.mobile.jwt_public_key'));

        try {
            $claims = JWT::decode($token, $key, ['RS256', 'HS256']);
        } catch (\Throwable) {
            abort(401);
        }

        return User::findOrFail($claims->sub);
    }
}
