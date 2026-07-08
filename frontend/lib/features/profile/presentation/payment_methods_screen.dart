import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';

class PaymentMethodsScreen extends StatelessWidget {
  const PaymentMethodsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(title: const Text('Metode Pembayaran')),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Tersimpan',
                  style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark, fontSize: 15, fontFamily: 'Plus Jakarta Sans'),
                ),
                const SizedBox(height: 12),

                // QRIS / E-Wallet linked
                _PaymentCard(
                  icon: Icons.qr_code_2_rounded,
                  title: 'QRIS',
                  subtitle: 'Scan dari aplikasi manapun',
                  isDefault: true,
                ),
                const SizedBox(height: 10),
                _PaymentCard(
                  icon: Icons.account_balance_wallet_rounded,
                  title: 'GoPay',
                  subtitle: 'Terhubung · +62 812 xxxx xxxx',
                  trailingBadge: 'AKTIF',
                ),
                const SizedBox(height: 10),
                _PaymentCard(
                  icon: Icons.account_balance_wallet_rounded,
                  title: 'OVO',
                  subtitle: 'Tidak aktif',
                  trailingBadge: 'NONAKTIF',
                  badgeColor: AppColors.textMuted,
                ),
                const SizedBox(height: 24),
                const Text(
                  'Transfer Bank',
                  style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark, fontSize: 15, fontFamily: 'Plus Jakarta Sans'),
                ),
                const SizedBox(height: 12),
                _PaymentCard(
                  icon: Icons.account_balance_rounded,
                  title: 'Bank BCA',
                  subtitle: '1234-5678-90 • a.n. ANDA',
                  trailingBadge: 'TERSIMPAN',
                  badgeColor: AppColors.info,
                ),
              ],
            ),
          ),

          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: ElevatedButton.icon(
              onPressed: () {},
              icon: const Icon(Icons.add_rounded, size: 18),
              label: const Text('Tambah Metode Baru'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.cream,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _PaymentCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final bool isDefault;
  final String? trailingBadge;
  final Color? badgeColor;

  const _PaymentCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    this.isDefault = false,
    this.trailingBadge,
    this.badgeColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: isDefault ? Border.all(color: AppColors.primary.withOpacity(0.3)) : null,
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.creamDark,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: AppColors.primary, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      title,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
                    ),
                    if (isDefault) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'DEFAULT',
                          style: TextStyle(color: AppColors.primary, fontSize: 9, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ],
                ),
                Text(subtitle, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
              ],
            ),
          ),
          if (trailingBadge != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: (badgeColor ?? AppColors.success).withOpacity(0.12),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                trailingBadge!,
                style: TextStyle(
                  color: badgeColor ?? AppColors.success,
                  fontWeight: FontWeight.bold,
                  fontSize: 10,
                  fontFamily: 'Plus Jakarta Sans',
                ),
              ),
            ),
        ],
      ),
    );
  }
}
