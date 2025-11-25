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
        // Check if table exists before adding constraint
        if (! Schema::hasTable('identity_verifications')) {
            return;
        }

        // Check if unique constraint already exists
        $connection = Schema::getConnection();
        $tableName = 'identity_verifications';
        $indexName = 'identity_verifications_citizen_id_unique';
        
        $indexes = $connection->getDoctrineSchemaManager()->listTableIndexes($tableName);
        if (isset($indexes[$indexName])) {
            return; // Constraint already exists
        }

        Schema::table('identity_verifications', function (Blueprint $table) {
            $table->unique('citizen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before dropping constraint
        if (! Schema::hasTable('identity_verifications')) {
            return;
        }

        Schema::table('identity_verifications', function (Blueprint $table) {
            $table->dropUnique(['citizen_id']);
        });
    }
};

