<?php

/**
 * Copyright (c) 2017-present, DocsPen.
 * All rights reserved.
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace DocsPen\Providers;

use Validator;
use DocsPen\Setting;
use DocsPen\Services\SettingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Custom validation methods
        Validator::extend(
            'is_image',
            function ($attribute, $value, $parameters, $validator) {
                $imageMimes = ['image/png', 'image/bmp', 'image/gif', 'image/jpeg', 'image/jpg', 'image/tiff', 'image/webp'];

                return in_array($value->getMimeType(), $imageMimes);
            }
        );

        \Blade::directive(
            'icon',
            function ($expression) {
                return "<?php echo icon($expression); ?>";
            }
        );

        // Allow longer string lengths after upgrade to utf8mb4
        \Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            SettingService::class,
            function ($app) {
                return new SettingService($app->make(Setting::class), $app->make('Illuminate\Contracts\Cache\Repository'));
            }
        );

        // Bugsnag Implementation
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);
    }
}
