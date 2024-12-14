<?php

namespace Braunson\FatSecret;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register bindings in the container.
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'fatsecret');

        // Use singleton instead of the deprecated share method
        $this->app->singleton('fatsecret', function ($app) {
            $oauth = new OAuthBase();

            $oauth->setSecret(config('fatsecret.secret'));

            $urlBuilder = new UrlBuilder(
                $oauth,
                new NonceFactory(),
                new TimestampFactory()
            );

            $urlBuilder->setKey(config('fatsecret.key'));

            return new FatSecret(
                new FatSecretApi(
                    $urlBuilder,
                    new Curl()
                )
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->publishes([$this->configPath() => config_path('fatsecret.php')], 'config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['fatsecret'];
    }

    /**
     * Get the path to the configuration file.
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__.'/../config/fatsecret.php';
    }
}
