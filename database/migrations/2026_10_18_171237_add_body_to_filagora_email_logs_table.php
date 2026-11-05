<?php

declare(strict_types=1);

use Filagora\Core\Support\Tables;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Tables::name('email_logs'), function (Blueprint $table): void {
            $table->mediumText('body')->nullable()->after('subject');
        });
    }

    public function down(): void
    {
        Schema::table(Tables::name('email_logs'), function (Blueprint $table): void {
            $table->dropColumn('body');
        });
    }
};
