import Sortable from 'sortablejs';
import '@fancyapps/fancybox';
import Cropper from 'cropperjs';
import Mustache from 'mustache';
import axios from 'axios';

window.adminAxios = axios.create({});
adminAxios.interceptors.request.use((config) => {
    config.url = '/admin/' + config.url;
    return config;
});

require('./../components/inputs/phone');

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

    $(document).on('pjax:end', function () {
        createSorting();
    });

    $(document).on('change', '#imageLoader', function () {
        let file = this.files[0];

        if ((file.size >> 20) >= 5) {
            return alert('Размер файла превышает 5 мегабайт!');
        }
        // if (file.type != 'image/jpeg') {
        //   return alert('Изображение должно быть в формате JPG!');
        // }

        if (file.type == 'image/jpeg') {
            let canvas = document.getElementById("imageCanvas"),
                ctx = canvas.getContext("2d"),
                reader = new FileReader;

            if (typeof cropper !== 'undefined') {
                cropper.destroy();
            }
            reader.onload = function (event) {
                var img = new Image;
                img.onload = function () {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);

                    cropper = new Cropper(canvas, cropperConfig);
                };
                img.src = event.target.result
            };
            reader.readAsDataURL(file);
            $.fancybox.open($('#crop-image'));
        }
    });

    // save cropped image
    $(document).on('click', '#save-cropped-image', function () {
        $.fancybox.getInstance('showLoading');
        cropper.getCroppedCanvas().toBlob((blob) => {
            let formData = new FormData();

            formData.append('croppedImage', blob); // , 'example.png'

            $.ajax('/api/admin/croppic/crop', {
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success(response) {
                    let previewHtml = Mustache.render(IMAGE_PREVIEW_TEMPLATE, response);

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
    }
}

$(document).on('click', '.js-productMediaCustomDataModalBtn', function () {
    let mediaId = $(this).data('id');
    let mediaVideo = $(this).data('video');
    let mediaISImidj = $(this).data('is_imidj');
    $('#js-productMediaCustomDataModalVideo').val(mediaVideo);
    $('#js-productMediaCustomDataModalIsImidj').prop('checked', mediaISImidj);
    $('.js-productMediaCustomDataModalSave').click(function () {
        appendNewInput("mediaData[" + mediaId + "][media_video]", $('#js-productMediaCustomDataModalVideo').val());
        appendNewInput("mediaData[" + mediaId + "][media_is_imidj]", $('#js-productMediaCustomDataModalIsImidj').is(':checked') ? 1 : 0);
        $('#js-productMediaCustomDataModal').modal('hide');
    });
    $(".la_checkbox").bootstrapSwitch({
        onText: 'Да',
        offText: 'Нет',
    });
    $('#js-productMediaCustomDataModal').modal('show');
});

$(document).on('click', '.js-productPromt', function (e) {
    e.preventDefault();
    let infoTextArr = [];
    $('#heel_txt').val() ? infoTextArr.push('высота каблука/подошвы ' + $('#heel_txt').val()) : '';
    $('#fabric_outsole_txt').val() ? infoTextArr.push('материал подошвы ' + $('#fabric_outsole_txt').val()) : '';
    $('#fabric_insole_txt').val() ? infoTextArr.push('материал стельки ' + $('#fabric_insole_txt').val()) : '';
    $('#fabric_inner_txt').val() ? infoTextArr.push('материал внутри ' + $('#fabric_inner_txt').val()) : '';
    $('#fabric_top_txt').val() ? infoTextArr.push('материал верха ' + $('#fabric_top_txt').val()) : '';
    let productPromtText = `
    Составь структурированное описание товара для интернет-магазина.
    План описания:
    1. Вводное первое предложение: категория товара, цвет и из какого материала сделан.
    2. Ключевая особенность товара и ее преимущество для покупателя. Объем 1-3 предложения.
    3. Описание товара используя список характеристик. Объем 3-7 предложений.
    4. Для каких ситуаций подойдет исходя из стиля. Объем 2-5 предложений.
    5. С какой одеждой сочетается. Объем 2-4 предложения.
    Исходные данные о товаре:
    1. Наименование - ` + $('.category_id').find(":selected").text().replace(/^-+\s/, '') + `
    2. Материал -` + $('.fabrics').find(":selected").toArray().map(item => item.text).join(', ') + `
    3. Цвет -` + $('.colors').find(":selected").toArray().map(item => item.text).join(', ') + `
    4. Ключевая особенность - ` + $('.key_features').val() + `
    5. Список характеристик: ` + infoTextArr.join(', ');
    window.navigator.clipboard.writeText(productPromtText);
    toastr.info('Описание скопировано в буфер обмена');
});

$(document).on('ifUnchecked', 'input', function () {
    $('.js-productTagList').find("[data-tag-id='" + $(this).val() + "']").remove();
});

$(document).on('ifChecked', 'input', function () {
    $('.js-productTagList').append("<li class=\"select2-selection__choice\" data-tag-id=\"" + $(this).val() + "\">" + $(this).data('name') + "</li>");
});

$(document).on('click', '.js-createOrderUser', function (e) {
    e.preventDefault();
    let json = {};
    adminAxios.post('orders/add-user-by-phone', {
        ...$('#js-createOrderUserModal input').serializeArray()
            .reduce(function (json, { name, value }) {
                json[name] = value;
                return json;
            }, {}),
        orderId: $('#js-orderId').val()
    }).then(response => {
        $('#js-createOrderUserModal').modal('hide');
        $("#js-userInfo").load(window.location.href + ' #js-userInfo');
        $("#js-orderUserId").val(response.data.id);
        toastr.success('Клиент успешно обновлен!');
    }).catch(function (error) {
        toastr.error(error?.response?.data?.message);
    });
});

$(document).on('click', '.js-changeOrderUserByPhone', function (e) {
    e.preventDefault();
    if (!$('#userChangePhone').hasClass("is-invalid")) {
        let phone = $('#userChangePhone').val();
        let orderId = $('#js-orderId').val();
        changeOrderUserByPhone(phone, orderId);
    }
});

function changeOrderUserByPhone(phone, orderId) {
    adminAxios.post('orders/change-user-by-phone', {
        phone: phone,
        orderId: orderId
    }).then(response => {
        $("#js-userInfo").load(window.location.href + ' #js-userInfo');
        $("#js-orderUserId").val(response.data.id);
        toastr.success('Клиент успешно обновлен!');
    }).catch(function (error) {
        toastr.error(error?.response?.data?.message);
    });
}

$(document).on('click', '.js-updateOrderUserAddress', function (e) {
  e.preventDefault();
  let json = {};
  adminAxios.post('orders/update-user-address', {
      ...$('#js-updateOrderUserAddress input').serializeArray()
          .reduce(function (json, { name, value }) {
              json[name] = value;
              return json;
          }, {}),
      userId: $('#js-orderUserId').val(),
      orderId: $('#js-orderId').val()
  }).then(response => {
      $('#js-updateOrderUserAddress').modal('hide');
      $("#js-orderAddress").load(window.location.href + ' #js-orderAddress');
      toastr.success('Адрес успешно обновлен!');
  }).catch(function (error) {
      toastr.error(error?.response?.data?.message);
  });
});

$(document).on('click', '#js-addNewOrderComment', function (e) {
  e.preventDefault();
  adminAxios.post('orders/add-order-comment', {
    orderId: $('#js-orderId').val(),
    comment: $('#js-newOrderComment').val()
  }).then(response => {
    $('#js-newOrderComment').val('');
    $.pjax.reload('#pjax-container');
    toastr.success('Комментарий успешно добавлен!');
  }).catch(function (error) {
    toastr.error(error?.response?.data?.message);
  });
});
