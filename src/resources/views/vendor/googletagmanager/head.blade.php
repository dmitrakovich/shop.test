<script>
window.userData = window.userData || {};
window.dataLayer = window.dataLayer || [];
@if($enabled)
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $id }}');

@unless(empty($userData->toArray()))
window.userData = {!! $userData->toJson() !!}
@endunless

document.addEventListener('DOMContentLoaded', function() {
    @unless(empty($dataLayer->toArray()))
    window.dataLayer.push({!! $dataLayer->toJson() !!});
    @endunless
    @foreach($pushData as $item)
    window.dataLayer.push({!! $item->toJson() !!});
    @endforeach
});
@endif
</script>
