<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('retention_requests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('manager_name');
            $table->string('requestor_name');
            $table->string('requestor_email');
            $table->foreignId('department_id')->constrained('departments'); // FIXME: setup on delete clause?
            $table->foreignId('authorizing_user_id')->nullable()->constrained('users'); // FIXME: setup on delete clause?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retention_requests');
    }
};
