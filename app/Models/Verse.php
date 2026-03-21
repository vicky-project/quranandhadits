<?php

namespace Modules\QuranAndHadits\Models;

use Illuminate\Database\Eloquent\Model;

class Verse extends Model
{
  protected $fillable = [
    'surah_id',
    'verse_number',
    'arabic_text',
    'latin_text',
    'translation',
    'audio'
  ];

  protected $casts = [
    'audio' => 'array',
  ];

  public function surah() {
    return $this->belongsTo(Surah::class);
  }
}