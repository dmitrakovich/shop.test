@if(!empty($banner->timer) || !empty($banner->spoiler))
  @php
    $timer_id      = uniqid();
    $timer_end_day = !empty($banner->timer) ? (floor((strtotime($banner->timer) - strtotime('now')) / (60 * 60 * 24))) : null;
  @endphp
  <div class="bg-dark p-3 d-flex justify-content-between align-items-center">
    @if(!empty($banner->timer) && (strtotime($banner->timer) > strtotime('now')))
      <div class="js-countdown" data-date-time="{{ date('m/d/Y H:i:s', strtotime($banner->timer)) }}">
        @if($timer_end_day)
          <span class="days">{{ $timer_end_day }}</span> д.
        @endif 
        <span class="hours">00</span>:<span class="minutes">00</span>:<span class="seconds">00</span>
      </div>
    @endif
    @if(!empty($banner->spoiler['btn_name']))
      <a class="text-white text-decoration-underline" data-toggle="collapse" href="#mobCatalogBannerCollapse_{{ $timer_id }}" role="button" aria-expanded="false" aria-controls="mobCatalogBannerCollapse_{{ $timer_id }}">{{ $banner->spoiler['btn_name'] ?? '' }}</a>
    @endif
  </div>
  @if(!empty($banner->spoiler['terms']))
    <div class="collapse multi-collapse" id="mobCatalogBannerCollapse_{{ $timer_id }}">
      <div class="card-body bg-dark text-white">
        {!! $banner->spoiler['terms'] ?? '' !!}
      </div>
    </div>
  @endif
@endif