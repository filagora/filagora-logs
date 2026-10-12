<?php

declare(strict_types=1);

use ForumKit\Core\Support\Tables;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `audits` is the table owen-it/laravel-auditing writes (the viewer reads what it recorded),
 * created in the shape that package ships with its default `user` morph prefix.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Tables::name('audits'), function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->morphs('auditable');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'user_type']);
        });

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
        Schema::dropIfExists(Tables::name('audits'));
    }
};
