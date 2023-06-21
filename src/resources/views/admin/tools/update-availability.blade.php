<div class="btn-group" data-toggle="buttons">
    <a href="{{ route('admin.automation.stock-update') }}" class="btn btn-sm btn-success">
        <i class="fa fa-refresh"></i>&nbsp;&nbsp;Обновить
    </a>
</div>
<span style="color:#AAAAAA; margin-left: 10px;">
    Последнее обновление {{ Cache::get('available_sizes_full_last_update', 'давно') }}
</span>

