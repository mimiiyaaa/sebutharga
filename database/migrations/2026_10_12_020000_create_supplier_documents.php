<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_documents', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);
            $table->unsignedInteger('supplier_id');
            $table->string('supplier_name');
            $table->string('document_no', 100);
            $table->date('document_date');
            $table->string('po_reference', 100)->nullable();
            $table->string('source', 30);
            $table->string('source_details', 1000)->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_mime', 100);
            $table->string('extraction_method', 100);
            $table->json('items');
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->unique(['kind', 'supplier_id', 'document_no'], 'supplier_document_number_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('supplier_documents'); }
};
