@if(!empty($end_time) && (strtotime($end_time) > strtotime('now')))
  @php
    $start = date_create(date('m/d/y h:i:s a', strtotime('now')));
    $end   = date_create(date('m/d/y h:i:s a', strtotime($end_time)));
    $diff  = date_diff($start, $end);
  @endphp
  <div class="js-countdown" data-date-time="{{ date('m/d/Y H:i:s', strtotime($end_time)) }}">
    @if(!empty($diff->d))
      <span class="days">{{ $diff->d }}</span> д.
    @endif 
    <span class="hours">{{ sprintf("%02d", ($diff->h ?? '00')) }}</span>:<span class="minutes">{{ sprintf("%02d", ($diff->i ?? '00')) }}</span>:<span class="seconds">{{ sprintf("%02d", ($diff->s ?? '00')) }}</span>
  </div>
@endif