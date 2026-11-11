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
        Schema::table('memberships', function (Blueprint $table) {
            $table->foreignId('renewal_of_id')->nullable()->after('membership_plan_id')->constrained('memberships')->nullOnDelete();
            $table->timestamp('expiry_reminder_sent_at')->nullable()->after('expires_at');
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropIndex(['status', 'expires_at']);
            $table->dropConstrainedForeignId('renewal_of_id');
            $table->dropColumn('expiry_reminder_sent_at');
        });
    }
};
