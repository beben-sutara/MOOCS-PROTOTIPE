<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->timestamp('issued_at');
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};
