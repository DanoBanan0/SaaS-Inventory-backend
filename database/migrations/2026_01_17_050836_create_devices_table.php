<?php

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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_code')->unique();
            $table->string('serial_number')->unique();
            $table->string('model');
            $table->string('brand');

            $table->foreignId('purchase_id')->constrained('purchases');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            
            $table->enum('status', ['available', 'assigned', 'maintenance', 'retired'])->default('available');
            $table->json('specs')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
