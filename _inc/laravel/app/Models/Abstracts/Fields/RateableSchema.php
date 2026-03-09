<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log};

abstract class RateableSchema extends BaseSchema
{
    protected function addFields(Blueprint $table): void
    {
        try {
            if (method_exists(parent::class, 'addFields')) parent::addFields($table);
            $table->text('integrity');
            $table->string('integrity_rating', 63)->default('average');
            $table->text('attendance');
            $table->string('attendance_rating', 63)->default('average');
            $table->text('customer_experience');
            $table->string('customer_experience_rating', 63)->default('average');
            $table->text('administration');
            $table->string('administration_rating', 63)->default('average')->nullable();
            $table->text('professionalism');
            $table->string('professionalism_rating', 63)->default('average')->nullable();
            $table->text('marketing')->nullable();
            $table->string('marketing_rating', 63)->default('average')->nullable();
            $table->string('overall_rating', 63)->default('average')->nullable();
            $table->json('rating')->nullable();
            $table->foreignId('rateable_created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        } catch (\Throwable $e) {
            Log::error(static::class . '::addFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    protected function dropFields(Blueprint $table): void
    {
        try {
            if (method_exists(parent::class, 'dropFields')) parent::dropFields($table);
            $table->dropColumn([
                'integrity',
                'integrity_rating',
                'attendance',
                'attendance_rating',
                'customer_experience',
                'customer_experience_rating',
                'administration',
                'administration_rating',
                'professionalism',
                'professionalism_rating',
                'marketing',
                'marketing_rating',
                'overall_rating',
                'rating',
                'rateable_created_by',
                'created_at',
                'updated_at'
            ]);
        } catch (\Throwable $e) {
            Log::error(static::class . '::dropFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }
}
