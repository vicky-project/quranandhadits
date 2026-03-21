<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up() {
    Schema::create('surahs', function (Blueprint $table) {
      $table->id();
      $table->integer('number')->unique();
      $table->string('name');
      $table->string('name_latin');
      $table->integer('number_of_verses');
      $table->string('place')->nullable();
      $table->string('meaning')->nullable();
      $table->text('description')->nullable();
      $table->json('audio_full')->nullable();
      $table->timestamps();
    });
  }

  public function down() {
    Schema::dropIfExists('surahs');
  }
};