<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_errors', function (Blueprint $table): void {
            $table->id();
            $table->string('fingerprint')->unique();
            $table->string('exception_class');
            $table->text('message')->nullable();
            $table->string('file')->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->text('trace')->nullable();
            $table->string('url')->nullable();
            $table->string('http_method')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at')->index();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_errors');
    }
};
