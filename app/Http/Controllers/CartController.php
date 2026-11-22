<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * A signed cart cookie, so guests can hold rooms before signing in.
 */
class CartController extends Controller
{
    private const COOKIE = 'booking_cart';

    public function show(Request $request)
    {
        return response()->json($this->read($request));
    }

    public function add(Request $request)
    {
        $data = $request->validate(['room_id' => ['required', 'integer']]);

        $cart = $this->read($request);
        $cart[] = ['room_id' => $data['room_id'], 'added_at' => now()->timestamp];

        return response()->json($cart)->cookie(self::COOKIE, $this->write($cart), 60 * 24);
    }

    private function read(Request $request): array
    {
        $raw = (string) $request->cookie(self::COOKIE);
        [$payload, $signature] = array_pad(explode('.', $raw, 2), 2, '');

        if ($payload === '' || ! hash_equals($this->sign($payload), $signature)) {
            return [];
        }

        $cart = unserialize(base64_decode($payload));

        return is_array($cart) ? $cart : [];
    }

    private function write(array $cart): string
    {
        $payload = base64_encode(serialize($cart));

        return $payload . '.' . $this->sign($payload);
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, config('app.name') . '-cart');
    }
}
