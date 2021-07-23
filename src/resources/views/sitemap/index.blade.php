<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <sitemap>
        <loc>{{ route('sitemap.products') }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>

    @foreach ($catalog1 as $filter)
        <sitemap>
            <loc>{{ route('sitemap.catalog.' . $filter) }}</loc>
            <lastmod>{{ $date }}</lastmod>
        </sitemap>
    @endforeach

    @foreach ($catalog2 as $filter)
        <sitemap>
            <loc>{{ route('sitemap.catalog.catalog2', $filter) }}</loc>
            <lastmod>{{ $date }}</lastmod>
        </sitemap>
    @endforeach

    @foreach ($catalog3 as $filters)
        <sitemap>
            <loc>{{ route('sitemap.catalog.catalog3', $filters) }}</loc>
            <lastmod>{{ $date }}</lastmod>
        </sitemap>
    @endforeach

    <sitemap>
        <loc>{{ route('sitemap.static') }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>

</sitemapindex>
