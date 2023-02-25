<script>
    window.User = @json(optional(auth()->user())->only('id'))

    function imageOnError(image) {
        image.onerror = '';
        image.src = '/storage/products/0/deleted.jpg';
    }
</script>
