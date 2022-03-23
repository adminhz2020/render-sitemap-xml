<?php

namespace SitemapRenderXML;

use Illuminate\Support\ServiceProvider;
use SitemapRenderXML\Console\Commands\CreateSiteMapXMLCommand;

class SitemapRenderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ .  '/../config/sitemap_render.php' => config_path('plugins/sitemap_render/sitemap_render.php')
        ]);
    }

    public function register()
    {
        $this->commands([
            CreateSiteMapXMLCommand::class
        ]);
    }
}