@if ((isset($banner->show_timer) && $banner->show_timer) ||
    (isset($banner->spoiler['show']) && $banner->spoiler['show']))
    @php
        $timer_id = uniqid();
    @endphp
    <div class="banner_timer"
        style="background: {{ $banner->getSpoilerBgColor() }}; color: {{ $banner->getSpoilerTextColor() }};">
        @if (!empty($banner->show_timer) && $banner->show_timer)
            @include('includes.timer', [
                'end_time' => $banner->end_datetime,
                'badgeCountdown' => true,
            ])
        @endif
        @if (!empty($banner->spoiler['btn_name']))
            <a class="banner_timer__spoiler-btn" style="color: {{ $banner->getSpoilerTextColor() }};"
                data-toggle="collapse" href="#mobCatalogBannerCollapse_{{ $timer_id }}" role="button"
                aria-expanded="false"
                aria-controls="mobCatalogBannerCollapse_{{ $timer_id }}">{{ $banner->spoiler['btn_name'] ?? '' }}</a>
        @endif
    </div>
    @if (!empty($banner->spoiler['terms']))
        <div class="collapse multi-collapse" id="mobCatalogBannerCollapse_{{ $timer_id }}">
            <div class="banner_timer__spoiler-body"
                style="background: {{ $banner->getSpoilerBgColor() }}; color: {{ $banner->getSpoilerTextColor() }};">
                {!! $banner->spoiler['terms'] ?? '' !!}
            </div>
        </div>
    @endif
@endif
