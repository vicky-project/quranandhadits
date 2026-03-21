<?php

namespace Modules\QuranAndHadits\Models;

use Illuminate\Database\Eloquent\Model;

class Hadith extends Model
{
  protected $fillable = ['book_id',
    'number',
    'arabic',
    'translation'];

  public function book() {
    return $this->belongsTo(HadithBook::class, 'book_id');
  }
}