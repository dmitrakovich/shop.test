@php
    $days = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($date)), date('Y', strtotime($date)));
@endphp
<div class="work-schedule table-responsive">
    <div class="work-schedule__nav">
        <a
            href="{{ request()->fullUrlWithQuery(['date' => date('Y-m', strtotime('-1 month', strtotime($date)))]) }}">предыдущий</a>
        <span>{{ date('F', strtotime($date)) }}</span>
        <a
            href="{{ request()->fullUrlWithQuery(['date' => date('Y-m', strtotime('+1 month', strtotime($date)))]) }}">следующий</a>
    </div>
    <table>
        <thead>
            <tr>
                <th width="140px">Менеджер</th>
                @for ($i = 1; $i <= $days; $i++)
                    <th>{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($schedule as $scheduleItem)
                <tr>
                    <td>{{ $admins[$scheduleItem['admin_user_id']] ?? null }}</td>
                    @for ($i = 1; $i <= $days; $i++)
                        @php
                            $i = str_pad($i, 2, '0', STR_PAD_LEFT);
                            $fullDate = $date . '-' . $i;
                        @endphp
                        <td>
                            <input type="hidden" name="schedule[{{ $fullDate }}][{{ $scheduleItem['admin_user_id'] }}]"
                                value="false">
                            <input type="checkbox" name="schedule[{{ $fullDate }}][{{ $scheduleItem['admin_user_id'] }}]"
                                @if ($workSchedules->where('admin_user_id', $scheduleItem['admin_user_id'])->where('date', $fullDate)->first()) checked @endif value="true">
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
    .work-schedule__nav {
        display: flex;
        justify-content: space-between;
        border-top: 1px solid #000;
        border-left: 1px solid #000;
        border-right: 1px solid #000;
    }

    .work-schedule__nav span {
        flex: 1;
        display: flex;
        justify-content: center;
        border-left: 1px solid #000;
        border-right: 1px solid #000;
        padding: 4px 8px;
    }

    .work-schedule__nav a,
    .work-schedule__nav a {
        padding: 4px 8px;
        text-align: center;
        width: 139px;
        cursor: pointer;
    }

    table {
        border-collapse: collapse;
        border: 1px solid #000;
        width: 100%;
    }

    table th,
    table td {
        border: 1px solid #000;
        padding: 4px 4px;
        text-align: center;
    }
</style>
