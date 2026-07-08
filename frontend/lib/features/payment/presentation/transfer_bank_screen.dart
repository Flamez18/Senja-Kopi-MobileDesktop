import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/models/order.dart';

class TransferBankScreen extends StatelessWidget {
  final Order order;
  const TransferBankScreen({super.key, required this.order});

  @override
  Widget build(BuildContext context) {
    // Add unique 3-digit suffix to total for virtual account
    final uniqueCode = (order.id % 900) + 100;
    final totalWithCode = order.total + uniqueCode;

    final banks = [
      {'name': 'Bank BCA', 'account': '1234567890', 'holder': 'PT Kopi Senja Nusantara', 'color': const Color(0xFF003087)},
      {'name': 'Bank Mandiri', 'account': '0987654321', 'holder': 'PT Kopi Senja Nusantara', 'color': const Color(0xFF003F88)},
    ];

    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(
        title: const Text('Transfer Bank'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (r) => false),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
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
                    CurrencyFormatter.toRupiah(totalWithCode),
                    style: const TextStyle(
                      color: Colors.white, fontSize: 32,
                      fontWeight: FontWeight.w900, fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.info_outline_rounded, color: AppColors.accentGold, size: 14),
                        const SizedBox(width: 6),
                        Text(
                          'Sudah termasuk kode unik +$uniqueCode',
                          style: const TextStyle(
                            color: AppColors.accentGold, fontSize: 11,
                            fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans',
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Transfer Notice
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.info.withOpacity(0.08),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.info.withOpacity(0.3)),
              ),
              child: const Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.info_outline_rounded, color: AppColors.info, size: 18),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Transfer sesuai nominal hingga 3 digit terakhir. Verifikasi dilakukan otomatis dalam 5–10 menit.',
                      style: TextStyle(color: AppColors.info, fontSize: 12, fontFamily: 'Plus Jakarta Sans'),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            const Text(
              'Pilih Rekening Tujuan',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
            ),
            const SizedBox(height: 10),

            // Bank Cards
            ...banks.map((bank) {
              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 30,
                      decoration: BoxDecoration(
                        color: (bank['color'] as Color),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Center(
                        child: Text(
                          bank['name'] == 'Bank BCA' ? 'BCA' : 'Mandiri',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 10),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            bank['name'] as String,
                            style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark, fontSize: 13, fontFamily: 'Plus Jakarta Sans'),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            bank['account'] as String,
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans', letterSpacing: 1),
                          ),
                          Text(
                            bank['holder'] as String,
                            style: const TextStyle(fontSize: 11, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () {
                        Clipboard.setData(ClipboardData(text: bank['account'] as String));
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('No. rekening ${bank["name"]} disalin!'),
                            behavior: SnackBarBehavior.floating,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            margin: const EdgeInsets.all(16),
                          ),
                        );
                      },
                      icon: const Icon(Icons.copy_rounded, color: AppColors.primary),
                      tooltip: 'Salin',
                    ),
                  ],
                ),
              );
            }),

            const SizedBox(height: 12),

            // Confirmation Image Area
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: SizedBox(
                width: double.infinity,
                height: 160,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    Image.network(
                      'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80',
                      fit: BoxFit.cover,
                    ),
                    Container(color: AppColors.primary.withOpacity(0.65)),
                    const Center(
                      child: Text(
                        'Siap Dinikmati.',
                        style: TextStyle(
                          color: Colors.white, fontSize: 22,
                          fontWeight: FontWeight.w900, fontFamily: 'Plus Jakarta Sans',
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 80),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.1), blurRadius: 12, offset: const Offset(0, -4))],
        ),
        child: ElevatedButton(
          onPressed: () {
            Navigator.pushNamedAndRemoveUntil(
              context, '/payment/success',
              (r) => r.settings.name == '/home',
              arguments: order,
            );
          },
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            foregroundColor: AppColors.cream,
            padding: const EdgeInsets.symmetric(vertical: 14),
          ),
          child: const Text('Saya Sudah Transfer →', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, fontFamily: 'Plus Jakarta Sans')),
        ),
      ),
    );
  }
}
