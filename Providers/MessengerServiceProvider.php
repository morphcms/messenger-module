<?php

namespace Modules\Messenger\Providers;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Messenger\Brokers\JanusBroker;
use RTippin\Messenger\Facades\Messenger;

/**
 * Laravel Messenger System, Created by: Richard Tippin.
 *
 * @link https://github.com/RTippin/messenger
 * @link https://github.com/RTippin/messenger-bots
 * @link https://github.com/RTippin/messenger-faker
 * @link https://github.com/RTippin/messenger-ui
 * @link https://github.com/RTippin/janus-client
 */
class MessengerServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected string $moduleName = 'Messenger';

    /**
     * @var string
     */
    protected string $moduleNameLower = 'messenger';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        // Register all provider models you wish to use in messenger.
        Messenger::registerProviders([
            User::class,
        ]);

        // Set the video call driver of your choosing.
        Messenger::setVideoDriver(JanusBroker::class);

        $this->app->afterResolving(Schedule::class, function (Schedule $scheduler) {
            $scheduler->command('messenger:calls:check-activity')->everyMinute();
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig(): void
    {
        $this->registerAndPublishConfig('messenger');
        $this->registerAndPublishConfig('messenger-ui');
        $this->registerAndPublishConfig('janus');
        $this->registerAndPublishConfig('websockets');
    }

    /**
     * @param $configName
     * @return void
     */
    protected function registerAndPublishConfig($configName): void
    {
        $this->publishes([
            module_path($this->moduleName, "Config/$configName.php") => config_path($configName.'.php'),
        ], 'config');
        $this->mergeConfigfrom(
            module_path($this->moduleName, "Config/$configName.php"),
            $configName
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
