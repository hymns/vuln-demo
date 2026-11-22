<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alice = User::forceCreate(['name' => 'Alice', 'email' => 'alice@stayeasy.test', 'password' => Hash::make('password')]);
        $bob = User::forceCreate(['name' => 'Bob', 'email' => 'bob@stayeasy.test', 'password' => Hash::make('password')]);
        User::forceCreate(['name' => 'Admin', 'email' => 'admin@stayeasy.test', 'password' => Hash::make('password'), 'is_admin' => true, 'role' => 'admin']);

        $deluxe = Room::create(['name' => 'Deluxe King', 'price_per_night' => 320, 'capacity' => 2]);
        $family = Room::create(['name' => 'Family Suite', 'price_per_night' => 540, 'capacity' => 5]);

        Coupon::create(['code' => 'WELCOME10', 'percent_off' => 10, 'max_uses' => 1]);

        foreach ([[$alice, $deluxe, 'BK-000001'], [$bob, $family, 'BK-000002']] as [$user, $room, $ref]) {
            Booking::create([
                'reference' => $ref,
                'user_id' => $user->id,
                'room_id' => $room->id,
                'guest_name' => $user->name,
                'guest_email' => $user->email,
                'check_in' => now()->addDays(7)->toDateString(),
                'check_out' => now()->addDays(9)->toDateString(),
                'guests' => 2,
                'total_amount' => $room->price_per_night * 2 * 2,
                'status' => 'pending',
                'guest_code' => sprintf('%06d', mt_rand(0, 999999)),
            ]);
        }
    }
}
