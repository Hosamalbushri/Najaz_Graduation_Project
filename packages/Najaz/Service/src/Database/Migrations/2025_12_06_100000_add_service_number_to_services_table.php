<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column already exists
        if (Schema::hasColumn('services', 'service_number')) {
            // Column exists, fill empty values and add unique constraint if not exists
            $this->fillEmptyServiceNumbers();
            
            // Check if unique constraint exists
            $indexes = DB::select("SHOW INDEXES FROM services WHERE Column_name = 'service_number' AND Non_unique = 0");
            if (empty($indexes)) {
                Schema::table('services', function (Blueprint $table) {
                    $table->unique('service_number');
                });
            }
        } else {
            // Column doesn't exist, create it
            Schema::table('services', function (Blueprint $table) {
                $table->string('service_number')->nullable()->after('id');
            });
            
            // Fill empty values
            $this->fillEmptyServiceNumbers();
            
            // Make it required and unique
            Schema::table('services', function (Blueprint $table) {
                $table->string('service_number')->nullable(false)->unique()->change();
            });
        }
    }

    /**
     * Fill empty service_number values with unique values.
     */
    protected function fillEmptyServiceNumbers(): void
    {
        $services = DB::table('services')
            ->whereNull('service_number')
            ->orWhere('service_number', '')
            ->get();
        
        foreach ($services as $service) {
            $serviceNumber = 'SRV-' . str_pad($service->id, 6, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness
            $counter = 1;
            while (DB::table('services')->where('service_number', $serviceNumber)->exists()) {
                $serviceNumber = 'SRV-' . str_pad($service->id, 6, '0', STR_PAD_LEFT) . '-' . $counter;
                $counter++;
            }
            
            DB::table('services')
                ->where('id', $service->id)
                ->update(['service_number' => $serviceNumber]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('services', 'service_number')) {
            Schema::table('services', function (Blueprint $table) {
                // Try to drop unique constraint if exists
                try {
                    $table->dropUnique(['service_number']);
                } catch (\Exception $e) {
                    // Constraint might not exist, ignore
                }
                $table->dropColumn('service_number');
            });
        }
    }
};

