{{-- blade-formatter-disable --}}
<?= '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL ?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="{{ now()->format('Y-m-d H:i') }}">
    <shop>
        <name>{{ $data->name }}</name>
        <company>{{ $data->company }}</company>
        <url>{{ $data->url }}/</url>
        <currencies>
@foreach ($data->currencies as $code => $rate)
            <currency id="{{ $code }}" rate="{{ $rate }}"/>
@endforeach
        </currencies>
        <delivery-options>
            <option cost="0" days="{{ $currency->country == 'BY' ? '2-4' : '8-10' }}" />
        </delivery-options>
        <categories>
@foreach ($data->categories as $category)
            <category id="{{ $category->id }}"@if (isset($category->parent_id)) parentId="{{ $category->parent_id }}"@endif>{{ $category->name }}</category>
@endforeach
        </categories>
        <offers>
@foreach ($data->offers as $offer)
            <offer id="{{ $offer->id }}" type="vendor.model" available="true">
                <url>{{ $offer->url }}</url>
                <name>{{ $offer->name }}</name>
                <price>{{ $offer->price }}</price>
@if ($offer->price < $offer->old_price)
                <oldprice>{{ $offer->old_price }}</oldprice>
@endif
                <currencyId>{{ $currency->code }}</currencyId>
@foreach ($offer->colors as $color)
                <param name="Цвет">{{ $color }}</param>
@endforeach
                <param name="Пол">Женский</param>
                <param name="Возраст">Взрослый</param>
@foreach ($offer->params as $name => $value)
                <param name="{{ $name }}">{{ $value }}</param>
@endforeach
                <categoryId>{{ $offer->category_id }}</categoryId>
@foreach ($offer->pictures as $picture)
                <picture>{{ $picture }}</picture>
@endforeach
                <store>false</store>
                <pickup>false</pickup>
                <delivery>true</delivery>
                <typePrefix>{{ $offer->type_prefix }}</typePrefix>
                <vendor>{{ $offer->vendor }}</vendor>
                <model>{{ $offer->model }}</model>
                <description><![CDATA[{!! $offer->description !!}]]></description>
                <sales_notes>{{ $offer->sales_notes }}</sales_notes>
@if($offer->video)
                <video>{{ $offer->video }}</video>
@endif
            </offer>
@endforeach
        </offers>
    </shop>
</yml_catalog>
