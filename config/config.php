<?php

return [
  'name' => 'QuranAndHadits',
  "hook" => [
    "quran" => [
      "enabled" => env("QURAN_HOOK_ENABLED", true),
      "service" => \Modules\CoreUI\Services\UIService::class,
      "name" => "main-apps",
    ],
    "hadits" => [
      "enabled" => env("HADITS_HOOK_ENABLED", true),
      "service" => \Modules\CoreUI\Services\UIService::class,
      "name" => "main-apps",

    ]
  ],
  "pagination" => [
    "per_page" => 20
  ]
];