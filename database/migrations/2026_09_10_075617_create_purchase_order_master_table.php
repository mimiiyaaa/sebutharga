<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Superseded by 2026_09_10_081900_create_purchase_order_master_table.
        // This migration remains as a no-op so existing databases can advance
        // their migration history without recreating the table.
    }

    public function down(): void
    {
        // The replacement migration owns this table.
    }
};
