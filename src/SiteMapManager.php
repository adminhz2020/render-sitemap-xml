<?php

namespace SitemapRenderXML;

use Illuminate\Support\Facades\DB;

class SiteMapManager
{
    public $sitemapModule;

    protected $currentSiteMap;

    protected $urlCollection = [];

    protected $maxHit = 25000;

    protected $siteMapRender;

    public function __construct(SiteMapRender $siteMapRender)
    {
        $this->siteMapRender = $siteMapRender;
        $this->sitemapModule = config('plugins.sitemap_render.sitemap_render');
    }

    public function index()
    {
        foreach ($this->sitemapModule as $module) {
            $this->setCurrentSiteMap($module);
            $this->renderUrls($module);
        }
    }

    protected function setCurrentSiteMap($module)
    {
        $siteMap = DB::table("sitemaps_manager")
            ->where("module_name", $module['module_name'])
            ->where("hit", "<", $this->maxHit)
            ->first();

        $quantitySiteMapSameModule = DB::table("sitemaps_manager")
            ->where("module_name", $module['module_name'])
            ->count();

        if (!$siteMap) {
            $dataSiteMap       = [
                "module_name" => $module['module_name'],
                "table_name"  => $module['table_name'],
                "file_name"   => $module['file_name'] . "_" . ($quantitySiteMapSameModule + 1),
                "hit"         => 0,
                "status"      => 0,
                "created_at"  => now(),
                "updated_at"  => now()
            ];
            $lastId            = DB::table("sitemaps_manager")->insertGetId($dataSiteMap);
            $dataSiteMap['id'] = $lastId;
            $siteMap           = (object)$dataSiteMap;
        }

        $this->currentSiteMap = $siteMap;
    }

    protected function renderUrls($module)
    {
        $urls = DB::table($module['table_name'])
            ->select(['id', 'check_sitemap', $module['field_url_seo']])
            ->where("check_sitemap", "=", 0)
            ->orderBy("id")
            ->limit(1000)
            ->get();

        $this->processUrls($urls, $module);

        $this->siteMapRender->setUrls($this->urlCollection)->render();

        $this->urlCollection  = null;
        $this->currentSiteMap = null;
    }

    protected function processUrls($urls, $module)
    {
        foreach ($urls as $url) {
            $this->currentSiteMap->hit++;

            if ($this->currentSiteMap->hit > $this->maxHit) {
                $this->updateSiteMap($this->currentSiteMap->id, [
                    'hit'    => $this->currentSiteMap->hit - 1,
                    'status' => 2
                ]);
                $this->setCurrentSiteMap($module);
                $this->currentSiteMap->hit++;
            }

            $this->setUrlCollection($url->id, $url->{$module['field_url_seo']});
        }

        $this->updateSiteMap($this->currentSiteMap->id, [
            'hit'    => $this->currentSiteMap->hit,
            'status' => $this->currentSiteMap->hit < $this->maxHit ? 1 : 2
        ]);
    }

    protected function setUrlCollection($urlId, $url)
    {
        $this->urlCollection[$this->currentSiteMap->id]['sitemap'] = $this->currentSiteMap;
        $this->urlCollection[$this->currentSiteMap->id]['data'][$urlId]  = $url;
    }

    protected function updateSiteMap($id, $updates = [])
    {
        DB::table("sitemaps_manager")
            ->where('id', '=', $id)
            ->update($updates);
    }
}