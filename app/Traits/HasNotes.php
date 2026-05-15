<?php
namespace Modules\QuranAndHadits\Traits;

use Nwidart\Modules\Facades\Module;

trait HasNotes {
  protected function notesJsConfig(): array
  {
    $available = Module::has('Notes') && Module::isEnabled('Notes');

    return [
      'notesAvailable' => $available,
      'notesEndpoint' => $available ? secure_url(config('notes.integration.endpoint')) : null
    ];
  }
}