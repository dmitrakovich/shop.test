<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach ($brands as $brand)
        <url>
            <loc>{{ url('catalog/' . $brand->slug) }}</loc>
            <lastmod>{{ $date }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

</urlset>
