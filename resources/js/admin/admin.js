/**
 * object for Cropper
 */
var cropper;
/**
 * config for cropper
 */
var cropperConfig = {
    viewMode: 2,
    modal: true,
    aspectRatio: 1,
    autoCropArea: 1
}

var imagePreviewTemplate = '<div class="file-preview-frame krajee-default">'
+'<div class="kv-file-content">'
    +'<img src="{{src}}"'
        +'class="file-preview-image kv-preview-data"'
        +'style="max-width:100%;max-height:100%;">'
+'</div>'
+'<div class="file-thumbnail-footer">'
    +'<div class="file-footer-caption" title="new">'
        +'<div class="file-caption-info">new</div>'
    +'</div>'
    +'<div class="file-actions">'
        +'<div class="file-footer-buttons">'
            +'<button type="button"'
                +'class="kv-file-remove btn btn-sm btn-kv btn-default btn-outline-secondary">'
                +'<i class="glyphicon glyphicon-trash"></i>'
            +'</button>'
            +'<button type="button" data-full="{{src}}"'
                +'class="kv-file-zoom btn btn-sm btn-kv btn-default btn-outline-secondary">'
                +'<i class="glyphicon glyphicon-zoom-in"></i>'
            +'</button>'
        +'</div>'
    +'</div>'
    +'<div class="clearfix"></div>'
+'</div>'
+'</div>';

$(function () {

    $(document).on('change', '#imageLoader', function () {
        let canvas = document.getElementById("imageCanvas"),
            ctx = canvas.getContext("2d"),
            reader = new FileReader;

        if (typeof cropper !== 'undefined') {
            cropper.destroy();
        }
        reader.onload = function(event) {
            var img = new Image;
            img.onload = function() {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);

                cropper = new Cropper(canvas, cropperConfig);
            };
            img.src = event.target.result
        };
        reader.readAsDataURL(this.files[0]);
        $.fancybox.open($('#crop-image'));
    });


    // save cropped image
    $(document).on('click', '#save-cropped-image', function () {
        $.fancybox.getInstance('showLoading');
        cropper.getCroppedCanvas().toBlob((blob) => {
            let formData = new FormData();

            formData.append('croppedImage', blob); // , 'example.png'

            $.ajax('/api/croppic/crop', {
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success(response) {
                    let preview = imagePreviewTemplate;

                    $(preview.replace('{{src}}', response)).appendTo('.js-images-area');
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'add_images[]',
                        value: response,
                    }).appendTo('form[class*="model-form-"]');
                    console.log(response);
                },
                error() {
                    alert('Upload error: ' + response);
                },
                complete() {
                    $.fancybox.getInstance('hideLoading');
                    $.fancybox.getInstance().close();
                }
            });
        });
    });

    // remove image
    $(document).on('click', '.kv-file-remove', function () {
        let imageId = $(this).data('id');
        if (imageId) {
            $('<input>').attr({
                type: 'hidden',
                name: 'remove_images[]',
                value: imageId,
            }).appendTo('form[class*="model-form-"]');
        }
        $(this).parents('.file-preview-frame').remove();
    });

    // hide/show mask
    $(document).on('click', '.js-mask-toggler', function () {
        $('.cropper-face').toggleClass('hide-mask');
    });


});

