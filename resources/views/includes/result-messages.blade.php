@if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <ul>
                    @foreach ($urrors->all() as $errorTxt)
                        <li>{{ $errorTxt }}</li>
                    @endforeach
                </ul>
                {{ $errors->first() }}
            </div>
        </div>
    </div>
@endif

@if (session('success'))
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="alert alert-success" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif