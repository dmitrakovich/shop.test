<table style="width: 100%;">
    <tbody>
        <tr>
            <td style="padding: 8px;"><b>Итого и среднее</b></td>
            <td style="width: 70px; padding: 8px;">{{ $data->sum('total_count') }}</td>
            <td style="width: 85px; padding: 8px;">{{ $data->sum('accepted_count') }}</td>
            <td style="width: 100px; padding: 8px;">{{ $data->sum('in_progress_count') }}</td>
            <td style="width: 105px; padding: 8px;">{{ $data->sum('purchased_count') }}</td>
            <td style="width: 100px; padding: 8px;">{{ $data->sum('canceled_count') }}</td>
            <td style="width: 95px; padding: 8px;">{{ $data->sum('returned_count') }}</td>
            <td style="width: 180px; padding: 8px;">{{ round($data->sum('total_purchased_price'), 2) }}</td>
            <td style="width: 140px; padding: 8px;">{{ round($data->avg(fn($item) => ($item->total_count ? round(($item->purchased_count / $item->total_count) * 100, 2) : 0)), 2) }} %</td>
            <td style="width: 180px; padding: 8px;">{{ round($data->sum('total_lost_price'), 2) }} BYN</td>
        </tr>
    </tbody>
</table>

<style>
    .box-footer {
        padding: 0;
    }
</style>
