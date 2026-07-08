<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('order_number')->unique();
            $table->enum('order_status', [
                'waiting_payment',
                'processing',
                'making',
                'ready',
                'completed',
                'cancelled',
            ])->default('waiting_payment');
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'expired',
            ])->default('pending');
            $table->enum('payment_method', ['cash', 'qris']);
            $table->text('notes')->nullable();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('service_fee')->default(2000);
            $table->unsignedInteger('total');
            $table->timestamp('paid_at')->nullable();
            $table->string('midtrans_snap_token')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
