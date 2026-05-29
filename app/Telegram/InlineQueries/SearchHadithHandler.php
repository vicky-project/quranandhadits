<?php
namespace Modules\QuranAndHadits\Telegram\InlineQueries;

use Illuminate\Support\Facades\Cache;
use Modules\Telegram\Services\Handlers\InlineQueries\BaseInlineQueryHandler;
use Modules\QuranAndHadits\Models\Hadith;

class SearchHadithHandler extends BaseInlineQueryHandler
{
  protected ?string $defaultParseMode = 'MarkdownV2';

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

    $cacheKey = "inline_hadith_search:" . md5($keyword);

    $results = Cache::remember($cacheKey, now()->addDays(7), function () use ($keyword) {
      // Gunakan fulltext untuk keyword minimal 3 karakter
      if (mb_strlen($keyword) >= 3) {
        $hadiths = Hadith::whereFullText(['arabic', 'translation'], $keyword)
        ->with('book')
        ->limit(10)
        ->get();
      } else {
        // Fallback LIKE untuk keyword pendek (tidak didukung fulltext)
        $hadiths = Hadith::where('translation', 'like', "%{$keyword}%")
        ->orWhere('arabic', 'like', "%{$keyword}%")
        ->with('book')
        ->limit(10)
        ->get();
      }

      if ($hadiths->isEmpty()) {
        return [];
      }

      $items = [];
      foreach ($hadiths as $hadith) {
        $bookName = $hadith->book->name ?? 'Kitab Hadits';
        $title = "{$bookName} No. {$hadith->number}";
        $messageText = "📜 *{$bookName} No. {$hadith->number}*\n\n";
        if (!empty($hadith->arabic)) {
          $messageText .= "{$hadith->arabic}\n\n";
        }
        $messageText .= $hadith->translation;

        $items[] = $this->makeArticleResult(
          id: "hadith_{$hadith->id}",
          title: $title,
          messageText: $messageText,
          description: mb_substr(strip_tags($hadith->translation), 0, 60) . '...',
          parseMode: $this->defaultParseMode
        );
      }
      return $items;
    });

    if (empty($results)) {
      return $this->successResult([
        $this->makeArticleResult(
          'notfound',
          'Tidak ditemukan',
          "Tidak ada hadits yang mengandung \"{$keyword}\"",
          'Coba kata kunci lain',
          parseMode: $this->defaultParseMode
        )
      ], ['cache_time' => 0]);
    }

    return $this->successResult($results, ['cache_time' => 60]);
  }
}