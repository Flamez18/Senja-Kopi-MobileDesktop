import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/utils/date_formatter.dart';
import '../../../core/models/order.dart';
import '../providers/order_provider.dart';

class OrderHistoryScreen extends StatefulWidget {
  const OrderHistoryScreen({super.key});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen>
    with SingleTickerProviderStateMixin, WidgetsBindingObserver {
  late TabController _tabController;
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    WidgetsBinding.instance.addObserver(this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refresh();
    });

    // Auto-refresh setiap 5 detik selama ada pesanan yang menunggu bayar
    _refreshTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      final orders = Provider.of<OrderProvider>(context, listen: false).orders;
      final hasPending = orders.any((o) => o.orderStatus == 'waiting_payment');
      if (hasPending && mounted) {
        Provider.of<OrderProvider>(context, listen: false).fetchOrders();
      }
    });
  }

  void _refresh() {
    if (mounted) {
      Provider.of<OrderProvider>(context, listen: false).fetchOrders();
    }
  }

  // Refresh ketika kembali ke layar ini dari background
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _refresh();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _refreshTimer?.cancel();
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(
        automaticallyImplyLeading: false,
        title: Row(
          children: [
            const Icon(Icons.location_on_rounded, color: AppColors.primary, size: 18),
            const SizedBox(width: 4),
            Expanded(
              child: Text(
                'Riwayat Pesanan',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans'),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          indicatorColor: AppColors.primary,
          labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans'),
          tabs: const [
            Tab(text: 'Semua'),
            Tab(text: 'Dibuat'),
            Tab(text: 'Siap'),
            Tab(text: 'Selesai'),
          ],
        ),
      ),
      body: Consumer<OrderProvider>(
        builder: (context, orderProvider, _) {
          if (orderProvider.isLoading) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }

          if (orderProvider.errorMessage != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.wifi_off_rounded, size: 48, color: AppColors.textMuted),
                  const SizedBox(height: 8),
                  Text(orderProvider.errorMessage!, style: const TextStyle(color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => orderProvider.fetchOrders(),
                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
                    child: const Text('Coba Lagi', style: TextStyle(color: AppColors.cream)),
                  ),
                ],
              ),
            );
          }

          return TabBarView(
            controller: _tabController,
            children: [
              _OrderList(orders: orderProvider.orders, filterStatus: null),
              _OrderList(orders: orderProvider.orders, filterStatus: 'making'),
              _OrderList(orders: orderProvider.orders, filterStatus: 'ready'),
              _OrderList(orders: orderProvider.orders, filterStatus: 'completed'),
            ],
          );
        },
      ),
    );
  }
}

class _OrderList extends StatelessWidget {
  final List<Order> orders;
  final String? filterStatus;

  const _OrderList({required this.orders, required this.filterStatus});

  @override
  Widget build(BuildContext context) {
    final filtered = filterStatus == null
        ? orders
        : orders.where((o) => o.orderStatus == filterStatus).toList();

    if (filtered.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.receipt_long_outlined, size: 64, color: AppColors.creamDark),
            SizedBox(height: 12),
            Text('Belum ada pesanan', style: TextStyle(color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans', fontSize: 16)),
          ],
        ),
      );
    }

    return RefreshIndicator(
      color: AppColors.primary,
      onRefresh: () => Provider.of<OrderProvider>(context, listen: false).fetchOrders(),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: filtered.length,
        itemBuilder: (ctx, idx) => _OrderCard(order: filtered[idx]),
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  final Order order;
  const _OrderCard({required this.order});

  @override
  Widget build(BuildContext context) {
    final statusColor = OrderProvider.statusColor(order.orderStatus);
    final statusLabel = OrderProvider.statusLabel(order.orderStatus);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Order number + status
          Row(
            children: [
              Expanded(
                child: Text(
                  order.orderNumber,
                  style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans'),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  statusLabel,
                  style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 11, fontFamily: 'Plus Jakarta Sans'),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          // Date and branch
          Text(
            DateFormatter.formatString(order.createdAt),
            style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
          ),
          const SizedBox(height: 2),
          if (order.branch != null)
            Text(
              order.branch!.name,
              style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
            ),
          const SizedBox(height: 10),
          // Items preview
          if (order.items != null && order.items!.isNotEmpty)
            Text(
              order.items!.map((i) => '${i.productName} x${i.quantity}').join(', '),
              style: const TextStyle(fontSize: 13, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          const Divider(height: 16, color: AppColors.creamDark),
          // Total + Action button
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                CurrencyFormatter.toRupiah(order.total),
                style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontSize: 15, fontFamily: 'Plus Jakarta Sans'),
              ),
              _actionButton(context, order),
            ],
          ),
        ],
      ),
    );
  }

  Widget _actionButton(BuildContext context, Order order) {
    if (order.orderStatus == 'ready') {
      return ElevatedButton(
        onPressed: () {
          Navigator.pushNamed(context, '/order-detail', arguments: order).then((_) {
            if (context.mounted) {
              Provider.of<OrderProvider>(context, listen: false).fetchOrders();
            }
          });
        },
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.success,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          minimumSize: Size.zero,
          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
        ),
        child: const Text('Ambil'),
      );
    } else if (order.orderStatus == 'completed') {
      return OutlinedButton(
        onPressed: () => Navigator.pushNamed(context, '/home'),
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.primary,
          side: const BorderSide(color: AppColors.primary),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          minimumSize: Size.zero,
          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
        ),
        child: const Text('Pesan Lagi'),
      );
    } else if (order.orderStatus == 'cancelled') {
      return OutlinedButton(
        onPressed: () {},
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.danger,
          side: const BorderSide(color: AppColors.danger),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          minimumSize: Size.zero,
          textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
        ),
        child: const Text('Bantuan'),
      );
    }
    return TextButton(
      onPressed: () {
        Navigator.pushNamed(context, '/order-detail', arguments: order).then((_) {
          if (context.mounted) {
            Provider.of<OrderProvider>(context, listen: false).fetchOrders();
          }
        });
      },
      child: const Text('Detail', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 12)),
    );
  }
}
