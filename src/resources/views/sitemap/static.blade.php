<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach ($routes as $route)
        <url>
            <loc>{{ route($route, [], true) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach

    @foreach ($routesWithParams as $route => $params)
        @foreach ($params as $param)
            <url>
                <loc>{{ route($route, $param, true) }}</loc>
                <changefreq>daily</changefreq>
                <priority>0.5</priority>
            </url>
        @endforeach
    @endforeach

</urlset>
