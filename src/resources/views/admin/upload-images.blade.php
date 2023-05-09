<div class="js-images-area" id="sortable-images-area">
    @foreach ($media as $image)
        <div class="file-preview-frame krajee-default" data-id="{{ $image->id }}">
            <div class="shop-file-content">
                <img src="{{ $image->getUrl('catalog') }}" class="file-preview-image kv-preview-data"
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
                        <button type="button" data-id="{{ $image->id }}"
                            data-video="{{ $image->getCustomProperty('video') }}"
                            data-is_imidj="{{ $image->getCustomProperty('is_imidj') }}"
                            class="btn btn-sm btn-kv btn-default btn-outline-secondary js-productMediaCustomDataModalBtn"
                            data-toggle="modal">
                            <i class="glyphicon glyphicon-edit"></i>
                        </button>
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

<!-- Modal -->
<div class="modal fade" id="js-productMediaCustomDataModal" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label">Ссылка на видео</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                            <input type="text" class="form-control"
                                id="js-productMediaCustomDataModalVideo" placeholder="Ссылка на видео">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3">Имиджевое</label>
                    <div class="col-sm-9" style="text-align: left;">
                        <input type="checkbox" id="js-productMediaCustomDataModalIsImidj"
                            class="la_checkbox" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Закрыть</button>
                <button type="button"
                    class="btn btn-primary js-productMediaCustomDataModalSave">Сохранить</button>
            </div>
        </div>
    </div>
</div>
