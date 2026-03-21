<?php

namespace Modules\QuranAndHadits\Models;

use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
  protected $fillable = [
    'number',
    'name',
    'name_latin',
    'number_of_verses',
    'place',
    'meaning',
    'description',
    'audio_full'
  ];

  protected $casts = [
    'audio_full' => 'array',
  ];

  public function verses() {
    return $this->hasMany(Verse::class);
  }
}