<?php
namespace Modules\QuranAndHadits\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Illuminate\Pagination\Paginator;
use Modules\QuranAndHadits\Telegram\InlineQueries\SearchHadithHandler;
use Modules\QuranAndHadits\Telegram\InlineQueries\SearchQuranHandler;
use Modules\Telegram\Services\Handlers\InlineQueryHandler;
use Modules\Telegram\Services\Support\TelegramApi;

class QuranAndHaditsServiceProvider extends ServiceProvider
{
  use PathNamespace;

  protected string $name = 'QuranAndHadits';

  protected string $nameLower = 'quranandhadits';

  /**
  * Boot the application events.
  */
  public function boot(): void
  {
    $this->registerCommands();
    $this->registerCommandSchedules();
    $this->registerTranslations();
    $this->registerConfig();
    $this->registerViews();
    $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

    // Quran hook main apps
    if (
      config($this->nameLower . ".hook.quran.enabled", false) &&
      class_exists($class = config($this->nameLower . ".hook.quran.service"))
    ) {
      $this->registerQuranHooks($class);
    }
    if (
      config($this->nameLower . ".hook.hadits.enabled", false) &&
      class_exists($class = config($this->nameLower . ".hook.hadits.service"))
    ) {
      $this->registerHadithsHooks($class);
    }

    if ($this->app->bound(InlineQueryHandler::class)) {
      $handler = $this->app->make(InlineQueryHandler::class);
      $this->registerTelegramInlineQueries($handler);
    }

    Paginator::useBootstrapFive();
  }

  /**
  * Register the service provider.
  */
  public function register(): void
  {
    $this->app->register(EventServiceProvider::class);
    $this->app->register(RouteServiceProvider::class);
  }

  protected function registerQuranHooks($hookService): void
  {
    $hookService::registerHook(
      config($this->nameLower . ".hook.quran.name"),
      $this->nameLower."::hooks.quran-app"
    );
  }

  protected function registerHadithsHooks($hookService): void
  {
    $hookService::registerHook(
      config($this->nameLower . ".hook.hadits.name"),
      $this->nameLower."::hooks.hadith-app"
    );
  }

  protected function registerTelegramInlineQueries(InlineQueryHandler $handler): void {
    $handler->registerHandler(
      new SearchHadithHandler($this->app->make(TelegramApi::class))
    );
    $handler->registerHandler(
      new SearchQuranHandler($this->app->make(TelegramApi::class))
    );
  }

  /**
  * Register commands in the format of Command::class
  */
  protected function registerCommands(): void
  {
    $this->commands([
      \Modules\QuranAndHadits\Console\FetchQuranData::class,
      \Modules\QuranAndHadits\Console\FetchHadithData::class
    ]);
  }

  /**
  * Register command Schedules.
  */
  protected function registerCommandSchedules(): void
  {
    // $this->app->booted(function () {
    //     $schedule = $this->app->make(Schedule::class);
    //     $schedule->command('inspire')->hourly();
    // });
  }

  /**
  * Register translations.
  */
  public function registerTranslations(): void
  {
    $langPath = resource_path('lang/modules/'.$this->nameLower);

    if (is_dir($langPath)) {
      $this->loadTranslationsFrom($langPath, $this->nameLower);
      $this->loadJsonTranslationsFrom($langPath);
    } else {
      $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
      $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
    }
  }

  /**
  * Register config.
  */
  protected function registerConfig(): void
  {
    $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

    if (is_dir($configPath)) {
      $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

      foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
          $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
          $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
          $segments = explode('.', $this->nameLower.'.'.$config_key);

          // Remove duplicated adjacent segments
          $normalized = [];
          foreach ($segments as $segment) {
            if (end($normalized) !== $segment) {
              $normalized[] = $segment;
            }
          }

          $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

          $this->publishes([$file->getPathname() => config_path($config)], 'config');
          $this->merge_config_from($file->getPathname(), $key);
        }
      }
    }
  }

  /**
  * Merge config from the given path recursively.
  */
  protected function merge_config_from(string $path, string $key): void
  {
    $existing = config($key, []);
    $module_config = require $path;

    config([$key => array_replace_recursive($existing, $module_config)]);
  }

  /**
  * Register views.
  */
  public function registerViews(): void
  {
    $viewPath = resource_path('views/modules/'.$this->nameLower);
    $sourcePath = module_path($this->name, 'resources/views');

    $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

    $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

    Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
  }

  /**
  * Get the services provided by the provider.
  */
  public function provides(): array
  {
    return [];
  }

  private function getPublishableViewPaths(): array
  {
    $paths = [];
    foreach (config('view.paths') as $path) {
      if (is_dir($path.'/modules/'.$this->nameLower)) {
        $paths[] = $path.'/modules/'.$this->nameLower;
      }
    }

    return $paths;
  }
}