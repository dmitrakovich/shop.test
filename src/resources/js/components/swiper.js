import { divide } from 'lodash';
import Swiper, { Autoplay, Pagination, Navigation, Thumbs } from 'swiper';
Swiper.use([Navigation, Autoplay, Thumbs]);
const intiSliders = function () {
    productSliders();
    simpleSlider();
};

const productSliders = function () {
    let productSliderThumb = new Swiper('.js-productSliderThumb', {
        direction: "horizontal",
        spaceBetween: 15,
        slidesPerView: 'auto',
        allowTouchMove: true,
        loop: false,
        freeMode: true,
        watchSlidesProgress: true,
        navigation: {
            nextEl: ".js-productSliderThumb-next",
            prevEl: ".js-productSliderThumb-prev",
        },
        breakpoints: {
            1024: {
                direction: "vertical",
                slidesPerView: 'auto',
                spaceBetween: 15,
            },
        }
    });
    new Swiper('.js-productSlider', {
        slidesPerView: 1,
        allowTouchMove: true,
        loop: false,
        thumbs: {
            swiper: productSliderThumb,
        },
        navigation: {
            nextEl: ".js-productSlider-next",
            prevEl: ".js-productSlider-prev",
        },
        on: {
            afterInit: function (elem) {
                let swiperIrames = elem.el.querySelectorAll('.js-swiperIrame');
                if (swiperIrames && swiperIrames.length) {
                    let youtubeScriptTag = document.createElement('script');
                    youtubeScriptTag.src = 'https://www.youtube.com/iframe_api';
                    document.body.appendChild(youtubeScriptTag);
                    window.onYouTubeIframeAPIReady = () => {
                        swiperIrames.forEach(element => {
                            let youtubeVideoId = element.dataset && element.dataset.id;
                            let childDiv = document.createElement('div');
                            element.appendChild(childDiv);
                            new YT.Player(childDiv, {
                                videoId: youtubeVideoId,
                                playerVars: {
                                    controls: 0
                                },
                                events: {
                                    onReady: (event) => {
                                        let isVideoPlaying = false;
                                        element.addEventListener('click', () => {
                                            isVideoPlaying ? event.target.stopVideo() : event.target.playVideo();
                                            isVideoPlaying = !isVideoPlaying;
                                        });
                                    }
                                }
                            });
                        });
                    }
                }
            },
        },
    });

    new Swiper('.js-productGroup', {
        slidesPerView: 'auto',
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: ".js-productGroup-next",
            prevEl: ".js-productGroup-prev",
        }
    });
}

const simpleSlider = function () {
    new Swiper('.js-simpleSlider', {
        slidesPerView: 1,
        slidesPerGroup: 1,
        loop: true,
        navigation: {
            nextEl: ".js-simpleSlider-next",
            prevEl: ".js-simpleSlider-prev",
        },
        autoplay: {
            pauseOnMouseEnter: true,
            disableOnInteraction: false
        },
        breakpoints: {
            1305: {
                slidesPerView: 5,
                slidesPerGroup: 5,
            },
            830: {
                slidesPerView: 4,
                slidesPerGroup: 4,
            },
            680: {
                slidesPerView: 3,
                slidesPerGroup: 3,
            },
            440: {
                slidesPerView: 2,
                slidesPerGroup: 2,
            }
        },
        on: {
            beforeInit: function (s) {
                if (s.$el[0] && s.$el[0].dataset && s.$el[0].dataset.autoplay) {
                    s.params.autoplay.delay = s.$el[0].dataset.autoplay || 4000;
                    s.params.autoplay.enabled = true;
                }
            }
        }
    })
}


export default intiSliders;
export {
    productSliders,
    simpleSlider
};
