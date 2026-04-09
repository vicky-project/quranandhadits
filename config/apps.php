<?php

return [
  [
    'id' => 'quran',
    'name' => 'Al-Quran',
    'description' => 'Baca ayat Al-Quran',
    'icon_class' => 'bi bi-journal-code',
    'render_type' => 'iframe',
    'render_config' => [
      'url' => env('APP_URL') . '/apps/quran'
    ]
  ],
  [
    'id' => 'hadith',
    'name' => 'Hadits',
    'description' => 'Koleksi hadits pilihan',
    'icon_class' => 'bi bi-journal-bookmark-fill',
    'render_type' => 'iframe',
    'render_config' => [
      'url' => env('APP_URL') . '/apps/hadith'
    ]
  ]
];