<?php

return [
  [
    'id' => 'quran',
    'name' => 'Al-Quran',
    'description' => 'Baca ayat suci Al-Quran',
    'icon_emoji' => '📖',
    'render_type' => 'iframe',
    'render_config' => [
      'url' => env('APP_URL') . '/apps/quran'
    ]
  ],
  [
    'id' => 'hadith',
    'name' => 'Hadits',
    'description' => 'Kumpulan hadits pilihan',
    'icon_emoji' => '📚',
    'render_type' => 'iframe',
    'render_config' => [
      'url' => env('APP_URL') . '/apps/hadith'
    ]
  ]
];