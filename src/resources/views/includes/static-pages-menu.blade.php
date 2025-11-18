@php
    $infoPages = \App\Models\InfoPage::get(['slug', 'name', 'icon'])->toArray();
    $currentSlug = $currentInfoPage['slug'] ?? null;
@endphp

@foreach ($infoPages as $item)
    <a class="col static-pages-menu-item {{ $currentSlug == $item['slug'] ? 'active' : null }}"
        href="{{ route('info', $item['slug']) }}">
        <img src="{{ $item['icon'] }}" class="img-fluid mr-3" alt="{{ $item['name'] }}">
        {{ $item['name'] }}
    </a>
@endforeach
