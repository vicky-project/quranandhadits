<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up() {
    Schema::create('verses', function (Blueprint $table) {
      $table->id();
      $table->foreignId('surah_id')->constrained()->onDelete('cascade');
      $table->integer('verse_number');
      $table->text('arabic_text');
      $table->text('latin_text')->nullable();
      $table->text('translation')->nullable();
      $table->json('audio')->nullable();
      $table->timestamps();

      $table->unique(['surah_id', 'verse_number']);
    });
  }

  public function down() {
    Schema::dropIfExists('verses');
  }
};