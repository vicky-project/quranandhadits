<?php
namespace Modules\QuranAndHadits\Telegram\InlineQueries;

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
    $keyword = trim(mb_substr($fullQuery, 6));

    if (empty($keyword)) {
      return $this->emptyResult('Ketik: quran [kata kunci]', 'quran_help');
    }

    $verses = Verse::where('translation', 'like', "%{$keyword}%")
    ->orWhere('arabic_text', 'like', "%{$keyword}%")
    ->with('surah')
    ->limit(10)
    ->get();

    if ($verses->isEmpty()) {
      return $this->successResult([
        $this->makeArticleResult(
          'notfound',
          'Tidak ditemukan',
          "Tidak ada ayat yang mengandung \"{$keyword}\"",
          'Coba kata kunci lain'
        )
      ], ['cache_time' => 0]);
    }

    $results = [];
    foreach ($verses as $verse) {
      $surahName = $verse->surah->name ?? 'Unknown';
      $title = "QS. {$surahName}: {$verse->verse_number}";
      $messageText = "📖 *QS. {$surahName}: {$verse->verse_number}*\n\n"
      . "{$verse->arabic_text}\n\n"
      . "_{$verse->latin_text}_\n\n"
      . "{$verse->translation}";

      $results[] = $this->makeArticleResult(
        id: "verse_{$verse->id}",
        title: $title,
        messageText: $messageText,
        description: mb_substr($verse->translation, 0, 60) . '...'
      );
    }

    return $this->successResult($results, ['cache_time' => 30]);
  }
}