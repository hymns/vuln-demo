<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoomImageController extends Controller
{
    private const BLOCKED_HOSTS = ['localhost', '127.0.0.1', '::1'];

    /**
     * Previews an image from a URL before an admin attaches it to a room.
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'starts_with:http://,https://'],
        ]);

        $host = strtolower((string) parse_url($data['url'], PHP_URL_HOST));
        abort_if(in_array($host, self::BLOCKED_HOSTS, true), 422, 'Local addresses are not allowed.');

        $response = Http::timeout(5)->get($data['url']);

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type') ?: 'application/octet-stream');
    }
}
