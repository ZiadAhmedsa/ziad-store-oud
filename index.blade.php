@extends('layouts.app')

@section('title', 'سلة التسوق')

@section('content')
    <div class="container" style="padding-top: 100px;">
        <h1 style="margin-bottom: var(--spacing-xl);">سلة التسوق</h1>

        @if($cartItems->count() > 0)
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-2xl);">
                <!-- Cart Items -->
                <div>
                    @foreach($cartItems as $item)
                        <div class="cart-item" id="cart-item-{{ $item['product_id'] }}">
                            <div class="cart-item-image">
                                @if($item['image'])
                                    <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}">
                                @else
                                    <img src="https://via.placeholder.com/100x100/211E1A/D4A574?text={{ urlencode($item['name']) }}"
                                        alt="{{ $item['name'] }}">
                                @endif
                            </div>
                            <div class="cart-item-details">
                                <h3 class="cart-item-title">
                                    <a href="{{ route('products.show', $item['product']->slug) }}">{{ $item['name'] }}</a>
                                </h3>
                                <div class="cart-item-price">{{ number_format($item['price'], 2) }} ر.س</div>
                                <div class="cart-item-quantity">
                                    <button type="button" class="quantity-btn"
                                        onclick="updateQuantity({{ $item['product_id'] }}, {{ $item['quantity'] - 1 }})">-</button>
                                    <span style="padding: 0 var(--spacing-md);">{{ $item['quantity'] }}</span>
                                    <button type="button" class="quantity-btn"
                                        onclick="updateQuantity({{ $item['product_id'] }}, {{ $item['quantity'] + 1 }})">+</button>
                                </div>
                            </div>
                            <div style="text-align: left;">
                                <div
                                    style="font-size: 1.25rem; font-weight: 700; color: var(--primary-gold); margin-bottom: var(--spacing-sm);">
                                    {{ number_format($item['subtotal'], 2) }} ر.س
                                </div>
                                <button onclick="removeFromCart({{ $item['product_id'] }})" class="cart-item-remove">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <form action="{{ route('cart.clear') }}" method="POST" style="margin-top: var(--spacing-lg);">
                        @csrf
                        <button type="submit" class="btn btn-ghost">
                            <i class="fas fa-trash"></i>
                            تفريغ السلة
                        </button>
                    </form>
                </div>

                <!-- Cart Summary -->
                <div>
                    <div class="cart-summary">
                        <h3 style="margin-bottom: var(--spacing-lg);">ملخص الطلب</h3>

                        <div class="cart-summary-row">
                            <span>المجموع الفرعي</span>
                            <span class="value" id="cart-subtotal">{{ number_format($cartTotal, 2) }} ر.س</span>
                        </div>
                        <div class="cart-summary-row">
                            <span>الشحن</span>
                            <span class="text-muted">يحسب عند الدفع</span>
                        </div>
                        <div class="cart-summary-row">
                            <span>الإجمالي</span>
                            <span class="value" id="cart-total">{{ number_format($cartTotal, 2) }} ر.س</span>
                        </div>

                        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg"
                            style="margin-top: var(--spacing-xl);">
                            <i class="fas fa-credit-card"></i>
                            إتمام الشراء
                        </a>

                        <a href="{{ route('products.index') }}" class="btn btn-ghost btn-block"
                            style="margin-top: var(--spacing-md);">
                            <i class="fas fa-arrow-right"></i>
                            متابعة التسوق
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-shopping-cart"></i></div>
                <h3 class="empty-state-title">السلة فارغة</h3>
                <p class="empty-state-description">لم تقم بإضافة أي منتجات للسلة بعد</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i>
                    تسوق الآن
                </a>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            async function updateQuantity(productId, quantity) {
                try {
                    const response = await fetch('{{ route("cart.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: quantity
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        location.reload();
                    } else {
                        showNotification(data.message, 'error');
                    }
                } catch (error) {
                    showNotification('حدث خطأ، حاول مرة أخرى', 'error');
                }
            }

            async function removeFromCart(productId) {
                try {
                    const response = await fetch('{{ route("cart.remove") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        document.getElementById('cart-item-' + productId).remove();
                        document.getElementById('cart-count').textContent = data.cartCount;

                        if (data.cartCount == 0) {
                            location.reload();
                        } else {
                            document.getElementById('cart-subtotal').textContent = data.cartTotal + ' ر.س';
                            document.getElementById('cart-total').textContent = data.cartTotal + ' ر.س';
                        }

                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                } catch (error) {
                    showNotification('حدث خطأ، حاول مرة أخرى', 'error');
                }
            }
        </script>
    @endpush
@endsection