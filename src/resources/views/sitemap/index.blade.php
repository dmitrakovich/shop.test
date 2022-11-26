<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <sitemap>
        <loc>{{ route('sitemap.products', [], true) }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>

    @foreach ($catalog1 as $filter)
    <sitemap>
        <loc>{{ route('sitemap.catalog.' . $filter, [], true) }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>
    @endforeach

    <sitemap>
        <loc>{{ route('sitemap.catalog.cities.categories', [], true) }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>

    <sitemap>
        <loc>{{ route('sitemap.catalog.cities.categories.tags', [], true) }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>

    @foreach ($catalog2 as $filter)
    <sitemap>
        <loc>{{ route('sitemap.catalog.catalog2', $filter, true) }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>
    @endforeach

    @foreach ($catalog3 as $filters)
    <sitemap>
        <loc>{{ route('sitemap.catalog.catalog3', $filters, true) }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>
    @endforeach

    <sitemap>
        <loc>{{ route('sitemap.static', [], true) }}</loc>
        <lastmod>{{ $date }}</lastmod>
    </sitemap>

</sitemapindex>
