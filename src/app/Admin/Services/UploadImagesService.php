<?php

namespace App\Admin\Services;

class UploadImagesService
{
    public function show($media)
    {
        return view('admin.upload-images', compact('media'))->render();
    }

    public function getImagesInput()
    {
        return '<div class="input-group-btn input-group-append">
            <div tabindex="500" class="btn btn-primary btn-file">
                <i class="glyphicon glyphicon-folder-open"></i>&nbsp;
                <span class="hidden-xs">Выбор файла</span>
                <input type="file" class="" name="photos[]" multiple id="imageLoader" accept="image/*">
            </div>
            <input type="hidden" name="add_images">
        </div>';
    }

    public function test()
    {
        dd('test');
    }
}
