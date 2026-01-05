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
        // Migrate existing rejection_reason data to the new table
        $serviceRequests = DB::table('service_requests')
            ->whereNotNull('rejection_reason')
            ->where('rejection_reason', '!=', '')
            ->get();

        foreach ($serviceRequests as $request) {
            DB::table('service_request_status_reasons')->insert([
                'service_request_id' => $request->id,
                'reason_type' => 'rejection',
                'reason' => $request->rejection_reason,
                'created_at' => $request->updated_at ?? now(),
                'updated_at' => $request->updated_at ?? now(),
            ]);
        }

        // Remove the rejection_reason column from service_requests table
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add the rejection_reason column back
        Schema::table('service_requests', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
        });

        // Migrate data back (only the latest rejection reason for each request)
        $latestRejections = DB::table('service_request_status_reasons')
            ->where('reason_type', 'rejection')
            ->orderBy('service_request_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('service_request_id');

        foreach ($latestRejections as $requestId => $reasons) {
            $latestReason = $reasons->first();
            DB::table('service_requests')
                ->where('id', $requestId)
                ->update(['rejection_reason' => $latestReason->reason]);
        }
    }
};

