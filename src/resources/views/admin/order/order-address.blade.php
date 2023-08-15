@if ($order)
    @php
        $resultOrderAddress = [];
        $resultOrderAddress[] = $order?->country?->name ?? null;
        $resultOrderAddress[] = $order->city ?? null;
        $resultOrderAddress[] = $order->user_addr ?? null;
        $resultOrderAddress = implode(', ', array_filter($resultOrderAddress, fn($item) => $item));
    @endphp
    @if ($resultOrderAddress)
        <h4>Адрес заказа</h4>
        {{ $resultOrderAddress }}
    @endif
    @if ($order->user)
        @php
            $lastAddress = $order?->user?->lastAddress;
        @endphp
        <h4>Адрес доставки</h4>
        <div class="row">
            <div class="col-md-10">
                <div class="@if ($lastAddress->approve) bg-success @else bg-danger @endif" id="js-orderUserAddress">
                    @php
                        $resultAddress = [];
                        $resultAddress[] = $lastAddress?->country?->name;
                        $resultAddress[] = $lastAddress?->region;
                        $resultAddress[] = $lastAddress?->city ? 'г. ' . $lastAddress?->city : null;
                        $resultAddress[] = $lastAddress?->zip;
                        $resultAddress[] = $lastAddress?->street ? 'ул. ' . $lastAddress?->street : null;
                        $resultAddress[] = $lastAddress?->house ? 'д. ' . $lastAddress?->house : null;
                        $resultAddress[] = $lastAddress?->corpus;
                        $resultAddress[] = $lastAddress?->room ? 'кв. ' . $lastAddress?->room : null;
                    @endphp
                    {{ implode(', ', array_filter($resultAddress, fn($item) => $item)) }}
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success" style="width: 100%" type="button" data-toggle="modal"
                    data-target="#js-updateOrderUserAddress">
                    Изменить
                </button>
            </div>
        </div>

        <div class="modal fade" id="js-updateOrderUserAddress" tabindex="-1" role="dialog"
            aria-labelledby="js-updateOrderUserAddressLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="js-updateOrderUserAddressLabel">Редактирование адреса</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Регион</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" name="addressRegion" class="form-control"
                                        value="{{ $lastAddress->region }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Город</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" name="addressCity" class="form-control"
                                        value="{{ $lastAddress->city }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Индекс</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" name="addressZip" class="form-control"
                                        value="{{ $lastAddress->zip }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Улица</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" name="addressStreet" class="form-control"
                                        value="{{ $lastAddress->street }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Дом</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" name="addressHouse" class="form-control"
                                        value="{{ $lastAddress->house }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Корпус</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" name="addressCorpus" class="form-control"
                                        value="{{ $lastAddress->corpus }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Квартира</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" name="addressRoom" class="form-control"
                                        value="{{ $lastAddress->room }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Подтверждение о проверке</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="checkbox" name="addressApprove" width="30px" height="30px"
                                        @checked($lastAddress->approve)>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="button" class="btn btn-primary js-updateOrderUserAddress">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
