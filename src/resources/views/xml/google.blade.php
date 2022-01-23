<?= '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL ?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>{{ $data->channel->title }}</title>
        <link>{{ $data->channel->link }}</link>
        <description>{{ $data->channel->description }}</description>
    </channel>
@foreach ($data->items as $item)
    <item>
        <g:id>{{ $item->id }}</g:id>
        <g:link>{{ $item->link }}</g:link>
        <g:size>{{ $item->size }}</g:size>
        <g:size_system>EU</g:size_system>
        <g:availability>{{ $item->availability }}</g:availability>
        <g:condition>new</g:condition>
@if ($item->price < $item->old_price)
        <g:price>{{ $item->old_price }} {{ $currency->code }}</g:price>
        <g:sale_price>{{ $item->price }} {{ $currency->code }}</g:sale_price>
@else
        <g:price>{{ $item->price }} {{ $currency->code }}</g:price>
@endif
@foreach ($item->images as $image)
@if ($loop->first)
        <g:image_link>{{ $image }}</g:image_link>
@else
        <g:additional_image_link>{{ $image }}</g:additional_image_link>
@endif
@endforeach
        <g:brand>{{ $item->brand }}</g:brand>
        <g:google_product_category>{{ $item->google_product_category }}</g:google_product_category>
        <g:product_type>{{ $item->product_type }}</g:product_type>
        <g:age_group>adult</g:age_group>
        <g:gender>female</g:gender>
        <g:description><![CDATA[{!! $item->description !!}]]></g:description>
        <g:title>{{ $item->title }}</g:title>
        <g:material>{{ $item->material }}</g:material>
        <g:color>{{ $item->color }}</g:color>
        <g:target_country>{{ $currency->country }}</g:target_country>
    </item>
@endforeach
</rss>
