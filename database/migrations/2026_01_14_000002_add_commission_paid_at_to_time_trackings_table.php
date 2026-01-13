<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These columns are used by AdminController commission dashboards.
        // Some environments may not have them yet; add them safely.
        if (!Schema::hasTable('time_trackings')) {
            return;
        }

        $connection = Schema::getConnection()->getDriverName();

        Schema::table('time_trackings', function (Blueprint $table) use ($connection) {
            if (!Schema::hasColumn('time_trackings', 'marketing_commission_paid_at')) {
                if ($connection === 'mysql' && Schema::hasColumn('time_trackings', 'marketing_partner_commission')) {
                    $table->timestamp('marketing_commission_paid_at')->nullable()->after('marketing_partner_commission');
                } else {
                    $table->timestamp('marketing_commission_paid_at')->nullable();
                }
            }

            if (!Schema::hasColumn('time_trackings', 'training_commission_paid_at')) {
                if ($connection === 'mysql' && Schema::hasColumn('time_trackings', 'training_center_commission')) {
                    $table->timestamp('training_commission_paid_at')->nullable()->after('training_center_commission');
                } else {
                    $table->timestamp('training_commission_paid_at')->nullable();
                }
            }

            if (!Schema::hasColumn('time_trackings', 'marketing_commission_stripe_transfer_id')) {
                $table->string('marketing_commission_stripe_transfer_id')->nullable();
            }

            if (!Schema::hasColumn('time_trackings', 'training_commission_stripe_transfer_id')) {
                $table->string('training_commission_stripe_transfer_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('time_trackings', function (Blueprint $table) {
            if (Schema::hasColumn('time_trackings', 'marketing_commission_paid_at')) {
                $table->dropColumn('marketing_commission_paid_at');
            }

            if (Schema::hasColumn('time_trackings', 'training_commission_paid_at')) {
                $table->dropColumn('training_commission_paid_at');
            }

            if (Schema::hasColumn('time_trackings', 'marketing_commission_stripe_transfer_id')) {
                $table->dropColumn('marketing_commission_stripe_transfer_id');
            }

            if (Schema::hasColumn('time_trackings', 'training_commission_stripe_transfer_id')) {
                $table->dropColumn('training_commission_stripe_transfer_id');
            }
        });
    }
};
