<?php

namespace Modules\QuranAndHadits\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\QuranAndHadits\Traits\FileDownloader;
use Modules\QuranAndHadits\Models\HadithBook;
use Modules\QuranAndHadits\Models\Hadith;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\Items;

class FetchHadithData extends Command
{
  use FileDownloader;

  protected $signature = 'app:hadith';
  protected $description = 'Fetch Hadith data from JSON using streaming';

  protected $url = 'https://vickyserver.my.id/data/hadiths/hadiths_data.json';
  protected $type = 'hadith';
  protected $config = [];

  public function __construct() {
    parent::__construct();
    $this->config = [
      'command' => $this,
      'max_retries' => 3,
      'http_timeout' => 600,
      'min_file_size' => 1024,
      'retry_delay' => 1000,
      'connect_timeout' => 30,
      'verify_ssl' => true,
    ];
  }

  public function handle() {
    $this->info('🚀 Fetching Hadith data...');

    $tempFile = null;
    try {
      $tempFile = $this->downloadData($this->url, null, true, $this->config);
      $this->info('✅ File downloaded: ' . $tempFile);

      // Count total books for progress bar
      $this->info('📊 Counting books...');
      $totalBooks = 0;
      foreach (Items::fromFile($tempFile, ['pointer' => '/hadiths']) as $_) {
        $totalBooks++;
      }
      $this->info("📊 Total books: {$totalBooks}");

      // Kosongkan tabel
      DB::statement("SET FOREIGN_KEY_CHECKS=0");
      HadithBook::truncate();
      Hadith::truncate();
      DB::statement("SET FOREIGN_KEY_CHECKS=1");

      DB::transaction(function () use ($tempFile, $totalBooks) {
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();

        $items = Items::fromFile($tempFile, [
          'pointer' => '/hadiths',
          'decoder' => new ExtJsonDecoder(true)
        ]);

        foreach ($items as $bookData) {
          // Insert book
          $book = HadithBook::create([
            'slug' => $bookData['id'],
            'name' => $bookData['name'],
            'total_hadiths' => $bookData['total_hadiths'],
          ]);

          // Batch insert hadiths
          $hadiths = [];
          foreach ($bookData['hadiths'] as $hadithData) {
            $hadiths[] = [
              'book_id' => $book->id,
              'number' => $hadithData['number'],
              'arabic' => $hadithData['arabic'],
              'translation' => $hadithData['translation'],
              'created_at' => now(),
              'updated_at' => now(),
            ];
          }
          Hadith::insert($hadiths);

          $progressBar->advance();
        }
        $progressBar->finish();
        $this->newLine();
      });

      $this->info('🎉 Hadith data imported successfully!');
    } catch (\Exception $e) {
      $this->error('❌ Error: ' . $e->getMessage());
      if ($tempFile) $this->cleanupTempFile($tempFile);
      return 1;
    }

    if ($tempFile) $this->cleanupTempFile($tempFile);
    return 0;
  }
}