<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach($cities as $city)
        @foreach ($categories as $category)
            @foreach ($tags as $tag)
                <url>
                    <loc>{{ route('shop-city', ['city' => $city->slug, 'path' => $category->slug . '/' . $tag->slug], true) }}</loc>
                    <lastmod>{{ $date }}</lastmod>
                    <changefreq>weekly</changefreq>
                    <priority>0.1</priority>
                </url>
            @endforeach
        @endforeach
    @endforeach

</urlset>
