<?php

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->ulid('order_id')->unique();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Voucher::class)->constrained();
            $table->unsignedDecimal('gross_amount', 15, 2);
            $table->text('note');
            $table->text('snap_token');
            $table->text('snap_url');
            $table->string('status', 25);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
