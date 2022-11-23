@if ((isset($banner->show_timer) && $banner->show_timer) ||
    (isset($banner->spoiler['show']) && $banner->spoiler['show']))
    @php
        $timer_id = uniqid();
    @endphp
    <div class="px-3 py-2 d-flex justify-content-between align-items-center"
        style="background: {{ $banner->getSpoilerBgColor() }}; color: {{ $banner->getSpoilerTextColor() }};">
        @if (!empty($banner->show_timer) && $banner->show_timer)
            @include('includes.timer', [
                'end_time' => $banner->end_datetime,
                'badgeCountdown' => true,
            ])
        @endif
        @if (!empty($banner->spoiler['btn_name']))
            <a class="text-decoration-underline" style="color: {{ $banner->getSpoilerTextColor() }};"
                data-toggle="collapse" href="#mobCatalogBannerCollapse_{{ $timer_id }}" role="button"
                aria-expanded="false"
                aria-controls="mobCatalogBannerCollapse_{{ $timer_id }}">{{ $banner->spoiler['btn_name'] ?? '' }}</a>
        @endif
    </div>
    @if (!empty($banner->spoiler['terms']))
        <div class="collapse multi-collapse" id="mobCatalogBannerCollapse_{{ $timer_id }}">
            <div class="card-body"
                style="background: {{ $banner->getSpoilerBgColor() }}; color: {{ $banner->getSpoilerTextColor() }};">
                {!! $banner->spoiler['terms'] ?? '' !!}
            </div>
        </div>
    @endif
@endif
