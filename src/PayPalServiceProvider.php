<?php

namespace PayPal;

use Illuminate\Support\ServiceProvider;

class PayPalServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load Translations
        if (is_dir(__DIR__ . '/../resources/lang') && is_array(scandir(__DIR__ . '/../resources/lang')))
            $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'PayPal');

        // Load Views
        // if not plublish the access to view is
        // return views('fatturaelettronica::nameview')
        if (is_dir(__DIR__ . '/../resources/views') && is_array(scandir(__DIR__ . '/../resources/views')))
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'PayPal');

        // Load Components
        // Class Base Components
        if (is_dir(__DIR__ . '/../src/View/Components') && is_array(scandir(__DIR__ . '/../src/View/Components')))
            $this->loadViewComponentsAs('PayPal', [
                //Alert::class,
            ]);

        // Anonymous Compnent
        // this willbe automatic loaded bye loadviews
        //$this->loadViewsFrom(__DIR__ . '/../resources/views', 'blogpackage');

        // Load Migrations
        if (is_dir(__DIR__ . '/../database/migrations') && is_array(scandir(__DIR__ . '/../database/migrations')))
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Registering package commands.
        if (is_dir(__DIR__ . '/../commands') && file_exists(__DIR__ . '/../commands/commands.php'))
            //$this->commands([]);
            $this->commands(file_get_contents(__DIR__ . '/../commands/commands.php'));

        // Load WEB routes
        if (is_dir(__DIR__ . '/../routes') && file_exists(__DIR__ . '/../routes/web.php'))
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // load API routes
        if (is_dir(__DIR__ . '/../routes') && file_exists(__DIR__ . '/../routes/api.php'))
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge the configuration file.
        // If configuration will be modifiable by user
        // uncomment relative publish method in bootForConsole
        // merge the config if exist
        if (is_dir(__DIR__ . '/../config/') && is_array(scandir(__DIR__ . '/../config/')) && file_exists(__DIR__ . '/../config/paypal.php'))
            $this->mergeConfigFrom(__DIR__ . '/../config/paypal.php', 'PayPal');

        // Register the service the package provides.
        $this->app->singleton('PayPal', function ($app) {
            return new PayPal;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['PayPal'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        /*$this->publishes([
            __DIR__ . '/../config/paypal.php' => config_path('paypal.php'),
        ], 'paypal.config');*/

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/stefanmcds-mnt'),
        ], 'paypal.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/stefanmcds-mnt'),
        ], 'paypal.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/stefanmcds-mnt'),
        ], 'paypal.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
