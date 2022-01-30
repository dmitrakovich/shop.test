<button type="button"
    @class([
        'btn',
        'position-absolute',
        'btn-favorite',
        'js-favorite',
        'active' => $favoriteState
    ])
    data-product-id="{{ $favoriteProductId }}"
    aria-label="Добавить в избранное"
    style="top: 0; left: 0;">
</button>
