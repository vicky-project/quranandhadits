<?php
namespace Modules\QuranAndHadits\Helpers;

class Helper
{
  public static function highlightText($text, $search, $color = 'rgba(255, 235, 59, 0.6)') {
    if (empty($search)) {
      return e($text);
    }

    $search = preg_quote($search, "/");

    return preg_replace_callback("/($search)/iu", function($matches) use($color) {
      return '<span class="highlight" style="background-color: '.$color.'">'.$matches[0].'</span>';
    }, e($text));
  }
}