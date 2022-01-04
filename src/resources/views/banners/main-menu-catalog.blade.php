@if (!empty($mainMenuCatalog))
    <a href="{{ $mainMenuCatalog->url }}">
        <img
            src="{{ $mainMenuCatalog->getFirstMediaUrl() }}"
            alt="{{ $mainMenuCatalog->title }}"
            class="img-fluid"
        />
    </a>
@endif
