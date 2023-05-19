<script>
    window.User = @json(optional(auth()->user())->only('id'))

    function imageOnError(image) {
        image.onerror = '';
        image.src = '/images/no-image.png';
    }
</script>
