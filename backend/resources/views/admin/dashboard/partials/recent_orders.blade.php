@if(Auth::user()->isSuperAdmin())
    @forelse($recentOrders as $order)
    <tr>
        <td>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="font-weight-700 text-decoration-none text-dark">
                {{ $order->order_number }}
            </a>
        </td>
        <td><span class="small text-muted">{{ $order->branch->name }}</span></td>
        <td class="font-weight-600">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
        <td>
            <span class="badge-status badge-{{ $order->order_status }}">
                {{ $order->order_status_label }}
            </span>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="4" class="text-center text-muted py-4">Belum ada pesanan masuk.</td>
    </tr>
    @endforelse
@else
    @forelse($recentOrders as $order)
    <tr>
        <td>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="font-weight-700 text-decoration-none text-dark">
                {{ $order->order_number }}
            </a>
        </td>
        <td>{{ $order->user->name }}</td>
        <td><span class="badge bg-light text-dark text-uppercase">{{ $order->payment_method }}</span></td>
        <td class="font-weight-600">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
        <td>
            <span class="badge-status badge-{{ $order->payment_status }}">
                {{ $order->payment_status_label }}
            </span>
        </td>
        <td>
            <span class="badge-status badge-{{ $order->order_status }}">
                {{ $order->order_status_label }}
            </span>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="text-center text-muted py-4">Belum ada pesanan di cabang ini.</td>
    </tr>
    @endforelse
@endif
