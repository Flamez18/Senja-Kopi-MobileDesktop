import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/models/order.dart';

class PaymentSuccessScreen extends StatelessWidget {
  final Order order;
  const PaymentSuccessScreen({super.key, required this.order});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgBody,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              const Spacer(),
              // Success Icon
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: AppColors.success.withOpacity(0.12),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded, color: AppColors.success, size: 80),
              ),
              const SizedBox(height: 20),
              const Text(
                'Pembayaran Berhasil',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
              ),
              const SizedBox(height: 8),
              const Text(
                'Kopimu sedang disiapkan dengan\nsepenuhatau dan barata kami.',
                style: TextStyle(fontSize: 14, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 32),

              // Order summary card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.07), blurRadius: 12, offset: const Offset(0, 3))],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('ID PESANAN', style: TextStyle(fontSize: 11, color: AppColors.textMuted, letterSpacing: 1, fontFamily: 'Plus Jakarta Sans')),
                        Text(
                          order.orderNumber,
                          style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans'),
                        ),
                      ],
                    ),
                    const Divider(height: 20, color: AppColors.creamDark),
                    if (order.items != null)
                      ...order.items!.map((item) => Padding(
                            padding: const EdgeInsets.only(bottom: 6),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    '${item.productName} x${item.quantity}',
                                    style: const TextStyle(fontSize: 13, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
                                  ),
                                ),
                                Text(
                                  CurrencyFormatter.toRupiah(item.subtotal),
                                  style: const TextStyle(fontSize: 13, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
                                ),
                              ],
                            ),
                          )),
                    const Divider(height: 20, color: AppColors.creamDark),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Subtotal', style: TextStyle(fontSize: 13, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                        Text(CurrencyFormatter.toRupiah(order.subtotal), style: const TextStyle(fontSize: 13, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Biaya Layanan', style: TextStyle(fontSize: 13, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                        Text(CurrencyFormatter.toRupiah(order.serviceFee), style: const TextStyle(fontSize: 13, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                      ],
                    ),
                    const Divider(height: 16, color: AppColors.creamDark),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Total Bayar', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans')),
                        Text(CurrencyFormatter.toRupiah(order.total), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans')),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        const Icon(Icons.check_circle_rounded, color: AppColors.success, size: 16),
                        const SizedBox(width: 6),
                        Text(
                          'Metode Pembayaran: ${order.paymentMethod.toUpperCase()}',
                          style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const Spacer(),

              // Action buttons
              ElevatedButton(
                onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/orders', (r) => r.settings.name == '/home'),
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: AppColors.cream),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('Lihat Status Pesanan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, fontFamily: 'Plus Jakarta Sans')),
                    SizedBox(width: 6),
                    Icon(Icons.arrow_forward_rounded, size: 16),
                  ],
                ),
              ),
              const SizedBox(height: 10),
              OutlinedButton(
                onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (r) => false),
                style: OutlinedButton.styleFrom(foregroundColor: AppColors.primary, side: const BorderSide(color: AppColors.primary)),
                child: const Text('Kembali ke Beranda', style: TextStyle(fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans')),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class PaymentFailureScreen extends StatelessWidget {
  final Order? order;
  const PaymentFailureScreen({super.key, this.order});

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
            const Text('Kopi Senja', style: TextStyle(fontSize: 15)),
          ],
        ),
        actions: [
          IconButton(icon: const Icon(Icons.notifications_outlined), onPressed: () {}),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            const Spacer(),
            // Failure icon
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.danger.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.error_outline_rounded, color: AppColors.danger, size: 80),
            ),
            const SizedBox(height: 20),
            const Text(
              'Pembayaran Gagal',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
            ),
            const SizedBox(height: 8),
            const Text(
              'Mohon maaf, transaksi Anda tidak dapat\ndiproses saat ini. Silakan coba kembali\natau hubungi koneksi internet Anda.',
              style: TextStyle(fontSize: 14, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            if (order != null)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('ID Pesanan', style: TextStyle(color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                    Text(order!.orderNumber, style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans')),
                  ],
                ),
              ),
            if (order != null)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Status', style: TextStyle(color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.danger.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text('Gagal', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans')),
                      ),
                    ],
                  ),
                ),
              ),
            const Spacer(),
            ElevatedButton(
              onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/checkout', (r) => r.settings.name == '/home'),
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: AppColors.cream),
              child: const Text('Coba Lagi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, fontFamily: 'Plus Jakarta Sans')),
            ),
            const SizedBox(height: 10),
            OutlinedButton(
              onPressed: () {},
              style: OutlinedButton.styleFrom(foregroundColor: AppColors.primary, side: const BorderSide(color: AppColors.primary)),
              child: const Text('Hubungi Bantuan', style: TextStyle(fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans')),
            ),
          ],
        ),
      ),
    );
  }
}
