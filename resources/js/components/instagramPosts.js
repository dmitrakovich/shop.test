// в зависимости от цифры (0-4) выбирается размер
// let imageSizes = {
//     "150": 0,
//     "240": 1,
//     "320": 2,
//     "480": 3,
//     "640": 4
// };

$(function () {
    let $instagramPostsBlock = $('.js-instagram-posts');

    if ($instagramPostsBlock.length)
    {
        let host = 'https://www.instagram.com/',
            username = 'barocco.by',
            postsCount = 6,
            colSize = 4, // для дектопа, для моб всегда 12
            xhr = new XMLHttpRequest();

        xhr.open("GET", host + username + '/');

        xhr.onload = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try{
                        var data = xhr.responseText.split("window._sharedData = ")[1].split("<\/script>")[0]; // тырим данные из js переменной _sharedData
                    }catch(error){
                        console.error("доступ к профилю видимо закрыт");
                        return;
                    }
                    data = JSON.parse(data.substr(0, data.length - 1)); // обрезать ';' и перевести в JSON
                    data = data.entry_data.ProfilePage;
                    if(typeof data === "undefined"){
                        console.error("Не удалось получить новостную ленту инстграм (many requests)");
                        return;
                    }
                    data = data[0].graphql.user.edge_owner_to_timeline_media.edges;

                    let html = '';
                    $.each(data, function (index, value) {
                        let node = value.node,
                            url = 'https://www.instagram.com/p/' + node.shortcode;
                        // image = node.thumbnail_src // max size
                        image = node.thumbnail_resources[3].src; // imageSizes
                        // switch (node.__typename) {
                        //     case "GraphSidecar": type_resource = "sidecar"; break;
                        //     case "GraphVideo": type_resource = "video"; break;
                        //     default: type_resource = "image";
                        // }
                        html += '<div class="col-12 col-sm-' + colSize + '">';
                        html += '<a href="' + url + '" rel="noopener" target="_blank">';
                        html += '<img src="' + image + '" alt="" class="img-fluid" />';
                        html += '</a><div class="row">';
                        html += '<div class="col-auto">@barocco</div>';
                        html += '<div class="col-auto ml-auto">&#10084;&nbsp;' + node.edge_liked_by.count + '</div>';
                        html += '</div></div>';
                        if (!--postsCount) {
                            return false;
                        }
                    });
                    // console.log(data);
                    $instagramPostsBlock.html(html);
                } else {
                    console.error("Request error. Response: " + xhr.statusText);
                }
            }
        };
        xhr.send();
    }
});


