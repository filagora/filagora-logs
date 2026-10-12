<?php

declare(strict_types=1);

use ForumKit\Core\Support\Tables;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The email log: one row per outgoing message, in the shape the live site's table has.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Tables::name('email_logs'), function (Blueprint $table): void {
            $table->id();
            $table->string('correlation_id')->nullable()->index();
            $table->string('status')->default('sending');
            $table->string('mailer')->nullable();
            $table->json('to');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->string('notification_class')->nullable();
            $table->string('message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Tables::name('email_logs'));
    }
};
