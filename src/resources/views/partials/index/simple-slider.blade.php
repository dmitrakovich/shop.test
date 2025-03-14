<h4 class="text-center">{{ $simpleSlider['title'] }}</h4>
<div class="swiper js-simpleSlider" data-autoplay="3000">
  <div class="swiper-wrapper">
    @foreach ($simpleSlider['products'] as $product)
    <div class="swiper-slide">
      @include('shop.slider-product', compact('product'))
    </div>
    @endforeach
  </div>
  <div class="js-simpleSlider-next swiper-button-next"></div>
  <div class="js-simpleSlider-prev swiper-button-prev"></div>
</div>
