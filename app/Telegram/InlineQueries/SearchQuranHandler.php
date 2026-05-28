<?php
namespace Modules\QuranAndHadits\Telegram\InlineQueries;

use Illuminate\Support\Facades\Cache;
use Modules\Telegram\Services\Handlers\InlineQueries\BaseInlineQueryHandler;
use Modules\QuranAndHadits\Models\Verse;

class SearchQuranHandler extends BaseInlineQueryHandler
{
  public function getName(): string
  {
    return 'quran_search';
  }

  public function getPattern(): string
  {
    return 'quran *';
  }

  protected function process(array $context): array
  {
    $fullQuery = $this->getQueryText($context);
    $keyword = trim(mb_substr($fullQuery, 6)); // hapus "quran "

    if (empty($keyword)) {
      return $this->emptyResult('Ketik: quran [kata kunci]', 'quran_help');
    }

    // Cache key berdasarkan keyword (bisa ditambah offset nanti)
    $cacheKey = "inline_quran_search:" . md5($keyword);

    // Coba ambil dari cache
    $results = Cache::remember($cacheKey, now()->addDays(7), function () use ($keyword) {
      $verses = Verse::where('translation', 'like', "%{$keyword}%")
      ->orWhere('arabic_text', 'like', "%{$keyword}%")
      ->with('surah')
      ->limit(10)
      ->get();

      if ($verses->isEmpty()) {
        return []; // cache hasil kosong
      }

      $items = [];
      foreach ($verses as $verse) {
        $surahName = $verse->surah->name ?? 'Unknown';
        $title = "QS. {$surahName}: {$verse->verse_number}";
        $messageText = "📖 *QS. {$surahName}: {$verse->verse_number}*\n\n"
        . "{$verse->arabic_text}\n\n"
        . "_{$verse->latin_text}_\n\n"
        . "{$verse->translation}";

        $items[] = $this->makeArticleResult(
          id: "verse_{$verse->id}",
          title: $title,
          messageText: $messageText,
          description: mb_substr($verse->translation, 0, 60) . '...'
        );
      }

      return $items;
    });

    if (empty($results)) {
      return $this->successResult([
        $this->makeArticleResult(
          'notfound',
          'Tidak ditemukan',
          "Tidak ada ayat yang mengandung \"{$keyword}\"",
          'Coba kata kunci lain'
        )
      ], ['cache_time' => 0]);
    }

    return $this->successResult($results, ['cache_time' => 60]);
  }
}