import Sortable from 'sortablejs';
import '@fancyapps/fancybox';
import Cropper from 'cropperjs';
import Mustache from 'mustache';

/**
 * object for Cropper
 */
var cropper;
/**
 * config for cropper
 */
var cropperConfig = {
  // viewMode: 2,
  modal: true,
  aspectRatio: 1,
  autoCropArea: 1
}

const IMAGE_PREVIEW_TEMPLATE = require('../../templates/admin/image-preview.html');

$(function () {
  // Sortable
  var sortableAreaId = document.getElementById('sortable-images-area');
  if (sortableAreaId) {
    var sortable = Sortable.create(sortableAreaId, {
      animation: 250,
      ghostClass: 'sortable-ghost'
    });
  }

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
          let previewHtml = Mustache.render(IMAGE_PREVIEW_TEMPLATE, {
            src: response
          });

          console.log(previewHtml);

          $(previewHtml).appendTo('.js-images-area');
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

  // set aspect ratio
  $(document).on('click', '.js-cropper-set-aspect-ratio', function () {
    cropper.setAspectRatio($(this).data('ratio'));
  });

  // hide/show mask
  $(document).on('click', '.js-mask-toggler', function () {
    $('.cropper-face').toggleClass('hide-mask');
  });

});

