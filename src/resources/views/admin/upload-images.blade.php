<div class="js-images-area" id="sortable-images-area">
    @foreach ($media as $image)
        <div class="file-preview-frame krajee-default" data-id="{{ $image->id }}">
            <div class="shop-file-content">
                <img src="{{ $image->getUrl('catalog') }}"
                    class="file-preview-image kv-preview-data"
                    style="max-width:100%;max-height:100%;">
            </div>
            <div class="file-thumbnail-footer">
                <div class="file-footer-caption" title="{{ $image->file_name }}">
                    <div class="file-caption-info">{{ $image->file_name }}</div>
                </div>
                <div class="file-actions">
                    <div class="shop-file-footer-buttons">
                        <button type="button" data-id="{{ $image->id }}"
                            class="kv-file-remove btn btn-sm btn-kv btn-default btn-outline-secondary">
                            <i class="glyphicon glyphicon-trash"></i>
                        </button>
                        <a data-fancybox="single" data-src="{{ $image->getUrl('full') }}"
                            class="kv-file-zoom btn btn-sm btn-kv btn-default btn-outline-secondary">
                            <i class="glyphicon glyphicon-zoom-in"></i>
                        </a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    @endforeach
</div>

<div style="display: none;" id="crop-image">
    <div class="form-group">
        <button type="button" class="btn btn-primary js-cropper-set-aspect-ratio" data-ratio="1">
            1 x 1
        </button>
        <button type="button" class="btn btn-primary js-cropper-set-aspect-ratio" data-ratio="0.6666">
            2 x 3
        </button>
        <button type="button" class="btn btn-default js-mask-toggler">
            Скрыть/показать маску
        </button>
    </div>
    <div class="form-group">
        <canvas id="imageCanvas" style="max-width: 100%; max-height: 80vh;"></canvas>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" id="save-cropped-image">Сохранить</button>
    </div>
</div>
