import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/models/order.dart';
import '../../order/providers/order_provider.dart';

class PaymentQrisScreen extends StatefulWidget {
  final Order order;
  const PaymentQrisScreen({super.key, required this.order});

  @override
  State<PaymentQrisScreen> createState() => _PaymentQrisScreenState();
}

class _PaymentQrisScreenState extends State<PaymentQrisScreen> {
  late Timer _timer;
  late Timer _statusPollingTimer;
  int _secondsRemaining = 15 * 60; // 15 minutes

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (_secondsRemaining > 0) {
        setState(() => _secondsRemaining--);
      } else {
        _timer.cancel();
      }
    });

    _statusPollingTimer = Timer.periodic(const Duration(seconds: 4), (_) async {
      if (!mounted) return;
      final provider = Provider.of<OrderProvider>(context, listen: false);
      final currentOrder = await provider.checkAndSyncPaymentStatus(widget.order.id);
      if (currentOrder != null && currentOrder.paymentStatus == 'paid' && mounted) {
        _statusPollingTimer.cancel();
        
        Navigator.pushNamedAndRemoveUntil(
          context, '/orders/${widget.order.id}',
          (r) => r.settings.name == '/home',
          arguments: currentOrder,
        );
        
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Pembayaran berhasil dikonfirmasi!'),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    });

    // Auto launch on start
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _launchPaymentUrl();
    });
  }

  @override
  void dispose() {
    _timer.cancel();
    _statusPollingTimer.cancel();
    super.dispose();
  }

  Future<void> _launchPaymentUrl() async {
    final token = widget.order.midtransSnapToken;
    if (token == null) return;
    final url = Uri.parse(token);
    try {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membuka portal: $e')),
        );
      }
    }
  }

  String get _timerText {
    final mins = (_secondsRemaining ~/ 60).toString().padLeft(2, '0');
    final secs = (_secondsRemaining % 60).toString().padLeft(2, '0');
    return '$mins:$secs';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(
        title: const Text('Pembayaran Digital'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (r) => false),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Total
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  const Text(
                    'TOTAL PEMBAYARAN',
                    style: TextStyle(color: AppColors.creamDark, fontSize: 11, fontFamily: 'Plus Jakarta Sans', letterSpacing: 1.2),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    CurrencyFormatter.toRupiah(widget.order.total),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 32,
                      fontWeight: FontWeight.w900,
                      fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.timer_outlined, color: AppColors.accentGold, size: 16),
                        const SizedBox(width: 6),
                        Text(
                          'Selesaikan sebelum $_timerText',
                          style: const TextStyle(
                            color: AppColors.accentGold,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            fontFamily: 'Plus Jakarta Sans',
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Redirection Portal Card
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.08), blurRadius: 14, offset: const Offset(0, 4))],
              ),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.05),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.payment_rounded, size: 64, color: AppColors.primary),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Selesaikan Pembayaran',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: AppColors.textDark,
                      fontSize: 18,
                      fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                  const SizedBox(height: 8),
                   Text(
                    'Kami mengalihkan Anda ke portal pembayaran Midtrans yang aman untuk menyelesaikan pembayaran ${widget.order.paymentMethod == 'qris' ? 'QRIS' : widget.order.paymentMethod == 'ewallet' ? 'E-Wallet' : 'Transfer Bank'}.',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppColors.textMuted,
                      fontFamily: 'Plus Jakarta Sans',
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    onPressed: _launchPaymentUrl,
                    icon: const Icon(Icons.open_in_browser_rounded),
                    label: const Text('Buka Portal Pembayaran'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.cream,
                      minimumSize: const Size(double.infinity, 50),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
                      ),
                      const SizedBox(width: 10),
                      Text(
                        'Menunggu pembayaran...',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.primary.withOpacity(0.8),
                          fontWeight: FontWeight.w600,
                          fontFamily: 'Plus Jakarta Sans',
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Navigation status check button
            OutlinedButton.icon(
              onPressed: () {
                Navigator.pushNamedAndRemoveUntil(
                  context, '/orders/${widget.order.id}',
                  (r) => r.settings.name == '/home',
                  arguments: widget.order,
                );
              },
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Cek Status Pesanan'),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.primary,
                side: const BorderSide(color: AppColors.primary),
                minimumSize: const Size(double.infinity, 48),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 24),

            // Steps Guide
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text(
                    'Panduan Pembayaran',
                    style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
                  ),
                  SizedBox(height: 12),
                  _StepItem(number: '1', text: 'Tekan tombol "Buka Portal Pembayaran" jika halaman tidak terbuka otomatis.'),
                  _StepItem(number: '2', text: 'Di portal Midtrans, pilih opsi pembayaran QRIS atau E-Wallet pilihan Anda.'),
                  _StepItem(number: '3', text: 'Lakukan pembayaran menggunakan aplikasi e-wallet atau m-banking Anda.'),
                  _StepItem(number: '4', text: 'Setelah selesai, kembali ke aplikasi ini. Status pembayaran Anda akan terupdate otomatis.'),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StepItem extends StatelessWidget {
  final String number;
  final String text;

  const _StepItem({required this.number, required this.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 24,
            height: 24,
            decoration: const BoxDecoration(
              color: AppColors.primary,
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                number,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textMuted,
                fontFamily: 'Plus Jakarta Sans',
              ),
            ),
          ),
        ],
      ),
    );
  }
}
