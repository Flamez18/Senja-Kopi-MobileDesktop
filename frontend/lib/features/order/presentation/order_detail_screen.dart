import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/models/order.dart';
import '../providers/order_provider.dart';

class OrderDetailScreen extends StatefulWidget {
  final Order order;
  const OrderDetailScreen({super.key, required this.order});

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  late Order _order;

  @override
  void initState() {
    super.initState();
    _order = widget.order;
    // Refresh the order detail
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await Provider.of<OrderProvider>(context, listen: false).fetchOrderDetail(_order.id);
      final refreshed = Provider.of<OrderProvider>(context, listen: false).currentOrder;
      if (refreshed != null && mounted) {
        setState(() => _order = refreshed);
      }
    });
  }

  static const List<String> _statusTimeline = [
    'waiting_payment',
    'processing',
    'making',
    'ready',
    'completed',
  ];

  @override
  Widget build(BuildContext context) {
    final currentStatusIndex = _statusTimeline.indexOf(_order.orderStatus);
    final statusColor = OrderProvider.statusColor(_order.orderStatus);
    final statusLabel = OrderProvider.statusLabel(_order.orderStatus);

    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(title: Text('Pesanan ${_order.orderNumber}')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [statusColor.withOpacity(0.15), statusColor.withOpacity(0.05)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: statusColor.withOpacity(0.3)),
              ),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(color: statusColor.withOpacity(0.15), shape: BoxShape.circle),
                    child: Icon(_statusIcon(_order.orderStatus), color: statusColor, size: 40),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    statusLabel,
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: statusColor, fontFamily: 'Plus Jakarta Sans'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Status Timeline
            if (_order.orderStatus != 'cancelled') ...[
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
                child: Column(
                  children: List.generate(_statusTimeline.length, (idx) {
                    final stepStatus = _statusTimeline[idx];
                    final isDone = currentStatusIndex >= idx;
                    final isCurrent = currentStatusIndex == idx;
                    return Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Column(
                          children: [
                            Container(
                              width: 20,
                              height: 20,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: isDone ? AppColors.primary : AppColors.creamDark,
                                border: isCurrent ? Border.all(color: AppColors.primary, width: 2) : null,
                              ),
                              child: isDone
                                  ? const Icon(Icons.check, size: 12, color: Colors.white)
                                  : null,
                            ),
                            if (idx < _statusTimeline.length - 1)
                              Container(
                                width: 2,
                                height: 28,
                                color: isDone ? AppColors.primary.withOpacity(0.3) : AppColors.creamDark,
                              ),
                          ],
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Padding(
                            padding: EdgeInsets.only(top: 2, bottom: idx < _statusTimeline.length - 1 ? 16 : 0),
                            child: Text(
                              OrderProvider.statusLabel(stepStatus),
                              style: TextStyle(
                                fontWeight: isCurrent ? FontWeight.bold : FontWeight.normal,
                                color: isDone ? AppColors.textDark : AppColors.textMuted,
                                fontFamily: 'Plus Jakarta Sans',
                              ),
                            ),
                          ),
                        ),
                      ],
                    );
                  }),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Branch info
            if (_order.branch != null) ...[
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
                child: Row(
                  children: [
                    const Icon(Icons.store_rounded, color: AppColors.primary),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_order.branch!.name, style: const TextStyle(fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans')),
                          Text(_order.branch!.address, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],

            // Order Items
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Item Pesanan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, fontFamily: 'Plus Jakarta Sans')),
                  const SizedBox(height: 10),
                  if (_order.items != null)
                    ..._order.items!.map((item) => Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text('${item.productName} x${item.quantity}', style: const TextStyle(fontSize: 13, fontFamily: 'Plus Jakarta Sans')),
                              ),
                              Text(CurrencyFormatter.toRupiah(item.subtotal), style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans')),
                            ],
                          ),
                        )),
                  const Divider(color: AppColors.creamDark),
                  _SummaryRow('Subtotal', CurrencyFormatter.toRupiah(_order.subtotal)),
                  const SizedBox(height: 6),
                  _SummaryRow('Biaya Layanan', CurrencyFormatter.toRupiah(_order.serviceFee)),
                  const Divider(color: AppColors.creamDark),
                  _SummaryRow('Total', CurrencyFormatter.toRupiah(_order.total), isBold: true),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  IconData _statusIcon(String status) {
    switch (status) {
      case 'waiting_payment': return Icons.timer_outlined;
      case 'processing': return Icons.receipt_outlined;
      case 'making': return Icons.coffee_maker_outlined;
      case 'ready': return Icons.check_circle_outline_rounded;
      case 'completed': return Icons.celebration_outlined;
      case 'cancelled': return Icons.cancel_outlined;
      default: return Icons.info_outline;
    }
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final bool isBold;

  const _SummaryRow(this.label, this.value, {this.isBold = false});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(fontSize: 13, color: isBold ? AppColors.textDark : AppColors.textMuted, fontWeight: isBold ? FontWeight.bold : FontWeight.normal, fontFamily: 'Plus Jakarta Sans')),
        Text(value, style: TextStyle(fontSize: 13, color: isBold ? AppColors.primary : AppColors.textDark, fontWeight: isBold ? FontWeight.bold : FontWeight.w600, fontFamily: 'Plus Jakarta Sans')),
      ],
    );
  }
}
