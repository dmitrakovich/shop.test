/*window.InstagramFeed = require('./components/InstagramFeed');

(function(){
    new InstagramFeed({
        'username': 'andrey_dmitrakovich',
        'container': document.getElementById("instaFeed"),
        'get_data': true,
        'display_profile': false,
        'display_biography': false,
        'display_gallery': true,
        'callback': function(data) {
            console.log(data);
            $('.test-gill-sans-mt').html(JSON.stringify(data, null, 2));
        },
        'styling': true,
        'items': 6,
        'items_per_row': 3,
        'margin': 1 
    });
})();*/

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
            username = 'andrey_dmitrakovich',
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

                        // доделать 

                        switch (node.__typename) {
                            case "GraphSidecar":
                                type_resource = "sidecar"
                                image = node.thumbnail_resources[3].src;
                                break;
                            case "GraphVideo":
                                type_resource = "video";
                                image = node.thumbnail_src
                                break;
                            default:
                                type_resource = "image";
                                image = node.thumbnail_resources[3].src;
                        }

                        html += '<div class="col-12 col-sm-' + colSize + ' p-2">';
                        html += '<a href="' + url + '" rel="noopener" target="_blank">';
                        html += '<img src="' + image + '" alt="" class="img-fluid" />';
                        html += '</a><div class="row">';
                        html += '<div class="col-auto">название</div>';
                        html += '<div class="col-auto ml-auto">&#10084;&nbsp;' + node.edge_liked_by.count + '</div>';
                        html += '</div></div>';
                        if (!--postsCount) {
                            return false;
                        }
                    });

                    // тут же их вывести
                    console.log(data);

                    $instagramPostsBlock.find('.row').html(html);
                } else {
                    console.error("Request error. Response: " + xhr.statusText);
                }
            }
        };
        
        xhr.send();
    }
});


