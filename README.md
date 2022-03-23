# Render Sitemap XML
This package will help render your website's sitemap xml

To use this package, please follow the steps below:

<b>Step 1:</b>
```
composer require workable-hz/render-sitemap-xml
```

<b>Step 2:</b>
```
php artisan vendor:publish --provider="SitemapRenderXML\SitemapRenderServiceProvider" 
```

<b>Step 3:</b>
```
php artisan config:cache
```

<b>Step 4:</b>
```
php artisan make:sitemap-xml
```

If you want to change module render on your website you just edit the config/sitemap_render.php
```
[
    "module_name"   => "module_name",
    "table_name"    => "table_name",
    'field_url_seo' => "field_url_seo",
    "file_name"     => "file_name"
]
```
<b>NOTE: every single record need 1 field "check_sitemap" with value is 0 or 1</b> 