<?php

namespace Modules\QuranAndHadits\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\QuranAndHadits\Traits\FileDownloader;
use Modules\QuranAndHadits\Models\Surah;
use Modules\QuranAndHadits\Models\Verse;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\Items;

class FetchQuranData extends Command
{
  use FileDownloader;

  protected $signature = 'app:quran';
  protected $description = 'Fetch Quran data from JSON and store in database using streaming';

  protected $url = 'https://vickyserver.my.id/data/quran/quran_data.json';
  protected $type = 'quran';
  protected $config = [];

  public function __construct() {
    parent::__construct();
    $this->config = [
      'command' => $this,
      'max_retries' => 3,
      'http_timeout' => 600,
      // 10 menit untuk download file besar
      'min_file_size' => 1024,
      'retry_delay' => 1000,
      'connect_timeout' => 30,
      'verify_ssl' => true,
      'http_headers' => [],
    ];
  }

  public function handle() {
    $this->info('🚀 Starting Quran data fetch with streaming...');

    $tempFile = null;

    try {
      // 1. Download data (tanpa progress bar, cukup tampilkan pesan via trait)
      $tempFile = $this->downloadData($this->url, null, true, $this->config);
      $this->info('✅ File downloaded to: ' . $tempFile);

      // 2. Hitung total surah untuk progress bar (streaming ringan)
      $this->info('📊 Counting total surah...');
      $totalSurahs = 0;
      $countItems = Items::fromFile($tempFile, ['pointer' => '/quran']);
      foreach ($countItems as $_) {
        $totalSurahs++;
      }
      $this->info("📊 Total surah found: {$totalSurahs}");

      // 3. Proses insert dengan progress bar
      $progressBar = $this->output->createProgressBar($totalSurahs);
      $progressBar->start();

      DB::transaction(function () use ($tempFile, $progressBar) {
        // Kosongkan tabel
        DB::statement("SET FOREIGN_KEY_CHECKS=0");
        Surah::truncate();
        Verse::truncate();
        DB::statement("SET FOREIGN_KEY_CHECKS=1");

        // Baca file JSON secara streaming
        $items = Items::fromFile($tempFile, [
          'pointer' => '/quran',
          'decoder' => new ExtJsonDecoder(true)
        ]);

        foreach ($items as $surahData) {
          // Simpan surah
          $surah = Surah::create([
            'number' => $surahData['number'],
            'name' => $surahData['name'],
            'name_latin' => $surahData['name_latin'],
            'number_of_verses' => $surahData['number_of_verses'],
            'place' => $surahData['place'] ?? null,
            'meaning' => $surahData['meaning'] ?? null,
            'description' => $surahData['description'] ?? null,
            'audio_full' => $surahData['audio_full'] ?? [],
          ]);

          // Batch insert ayat
          $verses = [];
          foreach ($surahData['verses'] as $verseData) {
            $verses[] = [
              'surah_id' => $surah->id,
              'verse_number' => $verseData['verse_number'],
              'arabic_text' => $verseData['arabic_text'],
              'latin_text' => $verseData['latin_text'] ?? null,
              'translation' => $verseData['translation'] ?? null,
              'audio' => $verseData['audio'] ?? [],
              'created_at' => now(),
              'updated_at' => now(),
            ];
          }
          Verse::insert($verses);

          // Update progress bar
          $progressBar->advance();
        }
      });

      $progressBar->finish();
      $this->newLine();
      $this->info('🎉 Quran data stored successfully!');

    } catch (\Exception $e) {
      $this->error('❌ Error: ' . $e->getMessage());
      if ($tempFile && file_exists($tempFile)) {
        $this->cleanupTempFile($tempFile);
      }
      return 1;
    }

    // Bersihkan file temporary
    if ($tempFile && file_exists($tempFile)) {
      $this->cleanupTempFile($tempFile);
    }

    return 0;
  }
}