<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->string('role')->default('guest');
            $table->boolean('is_admin')->default(false);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->unsignedSmallInteger('capacity');
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedTinyInteger('percent_off');
            $table->unsignedInteger('max_uses');
            $table->unsignedInteger('uses')->default(0);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('room_id')->constrained();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('guests');
            $table->text('notes')->nullable();
            $table->string('coupon_code')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('guest_code', 6);
            $table->string('invoice_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('rooms');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'is_admin']);
        });
    }
};
