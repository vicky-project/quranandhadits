<?php
namespace Modules\QuranAndHadits\Telegram\InlineQueries;

use Modules\Telegram\Services\Handlers\InlineQueries\BaseInlineQueryHandler;
use Modules\QuranAndHadits\Models\Hadith;

class SearchHadithHandler extends BaseInlineQueryHandler
{
  public function getName(): string
  {
    return 'hadith_search';
  }

  public function getPattern(): string
  {
    return 'hadits *';
  }

  protected function process(array $context): array
  {
    $fullQuery = $this->getQueryText($context);
    $keyword = trim(mb_substr($fullQuery, 7));

    if (empty($keyword)) {
      return $this->emptyResult('Ketik: hadits [kata kunci]', 'hadits_help');
    }

    $hadiths = Hadith::where('translation', 'like', "%{$keyword}%")
    ->orWhere('arabic', 'like', "%{$keyword}%")
    ->with('book')
    ->limit(10)
    ->get();

    if ($hadiths->isEmpty()) {
      return $this->successResult([
        $this->makeArticleResult(
          'notfound',
          'Tidak ditemukan',
          "Tidak ada hadits yang mengandung \"{$keyword}\"",
          'Coba kata kunci lain'
        )
      ], ['cache_time' => 0]);
    }

    $results = [];
    foreach ($hadiths as $hadith) {
      $bookName = $hadith->book->name ?? 'Kitab Hadits';
      $title = "HR. {$bookName} No. {$hadith->number}";
      $messageText = "📜 *{$bookName} No. {$hadith->number}*\n\n";
      if (!empty($hadith->arabic)) {
        $messageText .= "{$hadith->arabic}\n\n";
      }
      $messageText .= $hadith->translation;

      $results[] = $this->makeArticleResult(
        id: "hadith_{$hadith->id}",
        title: $title,
        messageText: $messageText,
        description: mb_substr(strip_tags($hadith->translation), 0, 60) . '...'
      );
    }

    return $this->successResult($results, ['cache_time' => 30]);
  }
}