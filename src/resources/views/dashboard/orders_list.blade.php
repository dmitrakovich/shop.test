@foreach ($orders as $order)
    <div class="dh_order_list__item">
        <div class="dh_order_list__top">
            <div class="dh_order_list__top-item">
                <span>Номер</span>
                <div>№{{ $order->id }}</div>
            </div>
            <div class="dh_order_list__top-item">
                <span>Дата создания</span>
                <div>{{ $order->created_at->format('от d.m.Y') }}</div>
            </div>
            <div class="dh_order_list__top-item">
                <span>Статус</span>
                <div>
                    {{ $order->status->getLabelForClient() }}
                    @if ($order->status->isWaitPayment())
                        <br>
                        @if (count($order->onlinePayments))
                            <a href="{{ $order->onlinePayments->first()->link }}">
                                Счет №{{ $order->onlinePayments->first()->payment_num }}
                            </a>
                        @endif
                    @elseif ($order->status->hasTracking() && $order->track->track_number)
                        <br>
                        <a @if ($order->track->track_link) href="{{ $order->track->track_link }}" @endif>
                            Трек№ {{ $order->track->track_number }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="dh_order_list__top-item">
                <span>Сумма</span>
                <div>{!! Currency::format($order->getTotalPrice(), $order->currency) !!}</div>
            </div>
        </div>
        <div class="dh_order_list__products">
            <a data-toggle="collapse" class="dh_order_list__products-more collapsed"
                href="#js-orderInfo-{{ $order->id }}" role="button" aria-expanded="false"
                aria-controls="js-orderInfo-{{ $order->id }}">
                <div class="dh_order_list__products-more_images">
                    @foreach ($order->itemsExtended as $item)
                        <span><img src="{{ $item->product->getFirstMediaUrl('default', 'catalog') }}"
                                alt="{{ $item->product->getFullName() }}"></span>
                    @endforeach
                </div>
                <span class="dh_order_list__products-more_arr"></span>
            </a>
            <div class="dh_order_list__products-content collapse" id="js-orderInfo-{{ $order->id }}">
                @foreach ($order->itemsExtended as $item)
                    <div class="dh_order_list__products-item">
                        <div class="dh_order_list__products-item_img">
                            <img src="{{ $item->product->getFirstMediaUrl('default', 'catalog') }}"
                                alt="{{ $item->product->getFullName() }}">
                        </div>
                        <div class="dh_order_list__products-item_descr">
                            <div><span>{{ $item->product->category->name ?? null }}:</span>
                                <span>{{ $item->product->brand->name ?? null }}</span>
                            </div>
                            <div><span>Размер:</span><span>{{ $item->size->name }}</span></div>
                            <div><span>Код:</span><span> {{ $item->product->sku ?? null }}</span></div>
                            <div>
                                <span>Статус:</span><span>{{ $item->status->getLabelForClient() }}</span>
                            </div>
                            <div>
                                <span>Цена:</span>
                                <span class="dh_order_list__products-price">
                                    @if ($item->discount)
                                        <span>
                                            <span class="dh_order_list__products-price_old">
                                                {!! Currency::format($item->old_price, $order->currency) !!}
                                            </span>
                                            <span
                                                class="dh_order_list__products-price_discount">{{ $item->discount }}%</span>
                                        </span>
                                    @endif
                                    <span class="dh_order_list__products-price_cur">
                                        {!! Currency::format($item->current_price, $order->currency) !!}
                                    </span>
                                </span>
                            </div>
                            @if ($item->status->isCompleted())
                                <button class="btn btn-dark js-leave-feedback-btn">Написать
                                    отзыв</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
