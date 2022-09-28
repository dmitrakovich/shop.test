@if(!empty($end_time) && (strtotime($end_time) > strtotime('now')))
    @php
        $start = date_create(date('m/d/y h:i:s a', strtotime('now')));
        $end = date_create(date('m/d/y h:i:s a', strtotime($end_time)));
        $diff = date_diff($start, $end);
    @endphp
    <span class="js-countdown" data-date-time="{{ date('m/d/Y H:i:s', strtotime($end_time)) }}">
        @if(isset($badgeCountdown) && $badgeCountdown)
            <div style="font-size: 20px;">
                @if(!empty($diff->d))
                    <span class="bg-white rounded text-center d-inline-block py-2 px-2" style="min-width: 40px;"><span class="days">{{ $diff->d }}</span> д.</span>
                    <span class="text-white">&nbsp;</span>
                @endif
                <span class="bg-white rounded text-center d-inline-block py-2 px-1 hours" style="min-width: 40px;">{{ sprintf("%02d", ($diff->h ?? '00')) }}</span>
                <span class="text-white font-weight-bold">:</span>
                <span class="bg-white rounded text-center d-inline-block py-2 px-1 minutes" style="min-width: 40px;">{{ sprintf("%02d", ($diff->i ?? '00')) }}</span>
                <span class="text-white font-weight-bold">:</span>
                <span class="bg-white rounded text-center d-inline-block py-2 px-1 seconds" style="min-width: 40px;">{{ sprintf("%02d", ($diff->s ?? '00')) }}</span>
            </div>
        @else
            @if(!empty($diff->d))
                <span class="days">{{ $diff->d }}</span> д.
            @endif
            <span class="hours">{{ sprintf("%02d", ($diff->h ?? '00')) }}</span>:<span class="minutes">{{ sprintf("%02d", ($diff->i ?? '00')) }}</span>:<span class="seconds">{{ sprintf("%02d", ($diff->s ?? '00')) }}</span>
        @endif
    </span>
@endif