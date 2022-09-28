@if(!empty($banner->show_timer) || !empty($banner->spoiler))
  @php
    $timer_id      = uniqid();
  @endphp
  <div class="bg-danger px-3 py-2 d-flex justify-content-between align-items-center">
    @if(!empty($banner->show_timer) && $banner->show_timer)
      @include('includes.timer', ['end_time' => $banner->end_datetime, 'badgeCountdown' => true])
    @endif
    @if(!empty($banner->spoiler['btn_name']))
      <a class="text-white text-decoration-underline" data-toggle="collapse" href="#mobCatalogBannerCollapse_{{ $timer_id }}" role="button" aria-expanded="false" aria-controls="mobCatalogBannerCollapse_{{ $timer_id }}">{{ $banner->spoiler['btn_name'] ?? '' }}</a>
    @endif
  </div>
  @if(!empty($banner->spoiler['terms']))
    <div class="collapse multi-collapse" id="mobCatalogBannerCollapse_{{ $timer_id }}">
      <div class="card-body bg-danger text-white">
        {!! $banner->spoiler['terms'] ?? '' !!}
      </div>
    </div>
  @endif
@endif