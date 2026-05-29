<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up() {
    Schema::table('verses', function (Blueprint $table) {
      $table->fullText(['arabic_text', 'translation']);
    });
    Schema::table('hadiths', function (Blueprint $table) {
      $table->fullText(['arabic', 'translation']);
    });
  }

  public function down() {
    Schema::table('hadiths', function(Blueprint $table) {
      $table->dropFullText(['arabic', 'translation']);
    });
    Schema::table('verses', function(Blueprint $table) {
      $table->dropFullText(['arabic_text', 'translation']);
    });
  }
};