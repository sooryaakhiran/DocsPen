<?php

/**
 * Copyright (c) 2017-present, DocsPen.
 * All rights reserved.
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace DocsPen\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //        Broadcast::routes();
        //
        //        /*
        //         * Authenticate the user's personal channel...
        //         */
        //        Broadcast::channel('DocsPen.User.*', function ($user, $userId) {
        //            return (int) $user->id === (int) $userId;
        //        });
    }
}
