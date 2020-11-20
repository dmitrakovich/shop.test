$(function () {
    $('.slider-for').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.slider-nav',
        responsive: [
            {
				breakpoint: 830,
				settings: {
                    dots: true,
                    fade: false,
				}
			}
        ]
    });
    $('.slider-nav').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        asNavFor: '.slider-for',
        arrows: false,
        focusOnSelect: true,
        responsive: [
			{
				breakpoint: 1600,
				settings: {
					slidesToShow: 5
				}
            },
            {
				breakpoint: 1305,
				settings: {
					slidesToShow: 4
				}
			},
            {
				breakpoint: 830,
				settings: "unslick"
			}
        ]
    });
});
