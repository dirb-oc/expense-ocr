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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->string('provider')->nullable();
            $table->string('document_number')->nullable();
            $table->date('document_date')->nullable();

            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();

            $table->string('currency', 3)->default('COP');

            $table->string('category')->default('other');

            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type');

            $table->text('ocr_text')->nullable();

            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};