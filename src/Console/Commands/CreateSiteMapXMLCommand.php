<?php

namespace SitemapRenderXML\Console\Commands;

use SitemapRenderXML\SiteMapManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class CreateSiteMapXMLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:sitemap-xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make site map xml';

    protected $siteMapManager;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SiteMapManager $siteMapManager)
    {
        parent::__construct();
        $this->siteMapManager = $siteMapManager;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->siteMapManager->index();

        $sitemap = App::make("sitemap");

        $sitemapSubs = DB::table('sitemaps_manager')->get();

        foreach ($sitemapSubs as $sitemapSub) {
            $pathFile = 'sitemap/' . $sitemapSub->file_name . '.xml.gz';
            $sitemap->addSitemap( URL::to($pathFile) );
        }

        $sitemap->store('sitemapindex','sitemap');

        return 1;
    }

}
