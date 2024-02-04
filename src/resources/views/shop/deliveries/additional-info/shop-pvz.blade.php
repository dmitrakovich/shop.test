<div class="additional-form-check-info">
    <select name="stock_id" class="form-control" style="width: unset; max-width: 100%;">
        @foreach ($shops as $id => $address)
            <option value="{{ $id }}">{{ $address }}</option>
        @endforeach
    </select>
</div>
