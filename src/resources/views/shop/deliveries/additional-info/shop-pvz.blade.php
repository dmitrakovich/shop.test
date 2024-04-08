<div class="additional-form-check-info">
    <select name="stock_id" class="form-control" style="width: unset; max-width: 100%;">
        @foreach ($shops as $id => $address)
            <option value="{{ $id }}">{{ $address }}</option>
        @endforeach
    </select>
</div>
<div class="additional-form-check-info text-muted font-size-12">
    * только при частичной предоплате ЕРИП (менеджер выставит счет после оформления заказа)
</div>
<div class="additional-form-check-info text-muted font-size-12">
    ** не более 3 ед. в заказе
</div>
