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

/**
 * object for Sortable
 */
var sortable;

const IMAGE_PREVIEW_TEMPLATE = require('../../templates/admin/image-preview.html');

$(function () {
  createSorting(); // Sortable

  $(document).on('pjax:end', function() {
    createSorting();
  });

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
          let previewHtml = Mustache.render(IMAGE_PREVIEW_TEMPLATE, {...response});

          $(previewHtml).appendTo('.js-images-area');
          appendNewInput('add_images[]', response.src);
          updateSorting();
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
      appendNewInput('remove_images[]', imageId);
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

/**
 * Append new input to form
 * @param {String} name input name
 * @param {String} value input value
 */
function appendNewInput(name, value = null) {
  return $('<input>').attr({
    type: 'hidden',
    name: name,
    value: value,
  }).appendTo('form[class*="model-form-"]');
}

/**
 * Create sorting
 */
function createSorting() {
  let sortableAreaId = document.getElementById('sortable-images-area');
  if (sortableAreaId) {
    sortable = Sortable.create(sortableAreaId, {
      animation: 250,
      ghostClass: 'sortable-ghost',
      onSort: (event) => updateSorting()
    });
  }
}

/**
 * Update Sorting
 */
function updateSorting() {
  if (typeof sortable !== 'undefined') {
    $('input[name="sorting"]').remove();
    appendNewInput('sorting', sortable.toArray().join('|'));
    // console.log(sortable.toArray());
  }
}
