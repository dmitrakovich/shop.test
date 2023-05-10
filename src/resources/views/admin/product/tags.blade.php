<div style="margin-top: -35px;">
    <div class="select2-container select2-container--default" style="width: 100%; margin: 0 0 10px;">
        <span class="select2-selection--multiple">
            <ul class="select2-selection__rendered js-productTagList">
                @foreach ($productTags as $productTag)
                    <li class="select2-selection__choice" data-tag-id="{{ $productTag->id }}">{{ $productTag->name }}</li>
                @endforeach
            </ul>
        </span>
    </div>
    <button class="btn btn-success btn-sm btn-kv js-productTagsModalBtn" type="button" data-toggle="modal"
        data-target="#js-productTagsModal">
        Изменить
    </button>

    <div class="modal fade" id="js-productTagsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row" style="display: flex; flex-wrap: wrap;">
                        @foreach ($tagGroups as $tagGroup)
                            <div class="col-sm-4">
                                {{ $tagGroup->name }}
                                @foreach ($tagGroup->tags as $tag)
                                    <div class="checkbox icheck">
                                        <label>
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                                class="tags" data-value="{{ $tag->id }}"
                                                data-name="{{ $tag->name }}" style="position: absolute; opacity: 0;"
                                                @if (!empty($productTags) && $productTags->where('id', $tag->id)->first()) checked @endif>
                                            {{ $tag->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        <input type="hidden" name="tags[]">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</div>
