<?php

namespace SitemapRenderXML;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use function file_get_contents;
use function rtrim;

class SiteMapRender
{
    /**
     * Danh sách url cần render
     * @var array
     */
    protected $urls = [];

    public function setUrls($urls = [])
    {
        $this->urls = $urls;
        return $this;
    }

    public function render()
    {
        if ($this->urls != [])
            foreach ($this->urls as $urlsSiteMap) {
            $fileName = $urlsSiteMap['sitemap']->file_name;
            $sitemap = App::make('sitemap');

            foreach ($urlsSiteMap['data'] as $urlId => $url) {
                $arrIdsToUpdate[] = $urlId;
                $sitemap->add(URL::to('/' . $url), Carbon::now(), 0.7, 'daily');
            }

            if ( File::exists(public_path('sitemap/' . $fileName . '.xml.gz')) ) {
                $content = $sitemap->render('xml')->getContent();
                $content = $this->reBuildContent($content, 'sitemap/' . $fileName . '.xml.gz');
            } else {
                $content = $sitemap->render('xml')->getContent();
            }
            file_put_contents(public_path('sitemap/' . $fileName . '.xml.gz'), gzencode($content, 9));

            $this->updateStatusSiteMapOfUrl($urlsSiteMap['sitemap'], $arrIdsToUpdate ?? []);
        }
    }

    public function reBuildContent($content, $path)
    {
        $content = explode("\n", $content);
        array_splice($content, 0, 3);
        $content = implode("\n", $content);

        $oldContent = file_get_contents('compress.zlib://'.public_path($path));
        $oldContent1 = substr($oldContent,0, -11);

        $newContent = $oldContent1 . $content;
        return $newContent;
    }

    public function updateStatusSiteMapOfUrl($siteMap, $arrIdsUpdate)
    {
        DB::table($siteMap->table_name)
            ->whereIn('id', $arrIdsUpdate)
            ->update(['check_sitemap' => 1]);
    }
}