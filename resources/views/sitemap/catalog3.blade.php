<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach ($categories as $category)
        @foreach ($anothers as $another)
            @foreach ($anothers2 as $another2)
            <url>
                <loc>{{ url($category->getUrl() . '/' . $another->slug. '/' . $another2->slug) }}</loc>
                <lastmod>{{ $date }}</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.7</priority>
            </url>
        @endforeach
        @endforeach
    @endforeach

</urlset>
