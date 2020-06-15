<?php

namespace DGZ_Uploader;

use Illuminate\Support\ServiceProvider;


class DGZ_UploaderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/config/dgz_uploader.php', 'dgz_uploader');


        $this->publishes([
            __DIR__.'/config/dgz_uploader.php' => config_path('dgz_uploader.php')
        ]);
    }


    public function register()
    {

    }
}