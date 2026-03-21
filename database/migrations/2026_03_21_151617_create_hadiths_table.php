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
    Schema::create('hadiths', function (Blueprint $table) {
      $table->id();
      $table->foreignId('book_id')->constrained('hadith_books')->onDelete('cascade');
      $table->integer('number');
      $table->text('arabic');
      $table->text('translation');
      $table->timestamps();

      $table->unique(['book_id', 'number']);
    });
  }

  /**
  * Reverse the migrations.
  */
  public function down(): void
  {
    Schema::dropIfExists('hadiths');
  }
};