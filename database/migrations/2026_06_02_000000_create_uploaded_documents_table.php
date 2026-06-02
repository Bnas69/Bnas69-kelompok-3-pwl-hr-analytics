<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('original_name');
            $table->string('stored_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension', 30)->nullable();
            $table->unsignedBigInteger('size');
            $table->string('storage_mode', 20)->default('database');
            $table->string('disk', 30)->nullable();
            $table->string('storage_path')->nullable();
            $table->longText('file_data')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('storage_mode');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_documents');
    }
};
