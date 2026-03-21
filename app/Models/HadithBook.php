<?php

namespace Modules\QuranAndHadits\Models;

use Illuminate\Database\Eloquent\Model;

class HadithBook extends Model
{
  protected $fillable = ['slug',
    'name',
    'total_hadiths'];

  public function hadiths() {
    return $this->hasMany(Hadith::class);
  }
}