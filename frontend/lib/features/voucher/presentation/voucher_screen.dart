import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../cart/providers/cart_provider.dart';

class VoucherScreen extends StatelessWidget {
  const VoucherScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

    // Dummy vouchers — in production, fetch from API
    final vouchers = [
      {
        'code': 'DISKON50',
        'title': 'Diskon 50%',
        'description': 'Berlaku untuk semua menu Latte',
        'discount': 15000,
        'type': 'Minuman',
        'badge': 'DISKON 50%',
        'color': const Color(0xFF2E7D32),
        'image': 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=300&q=80',
      },
      {
        'code': 'ESPRESOONLY',
        'title': 'Potongan 15rb',
        'description': 'Minimal pembelian 60rb',
        'discount': 15000,
        'type': 'Semua',
        'badge': 'EKSPRESO ONLY',
        'color': const Color(0xFF3E1F00),
        'image': 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=300&q=80',
      },
      {
        'code': 'BUY1GET1',
        'title': 'Buy 1 Get 1 Free',
        'description': 'Dapatkan minuman gratis satu pilihan',
        'discount': 28000,
        'type': 'Terbatas',
        'badge': 'BUY 1 GET 1',
        'color': const Color(0xFF1565C0),
        'image': 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=300&q=80',
      },
    ];

    return DefaultTabController(
      length: 4,
      child: Scaffold(
        backgroundColor: AppColors.bgBody,
        appBar: AppBar(
          title: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Voucher & Diskon', style: TextStyle(fontSize: 18)),
              Text(
                'Nikmati aroma kopi terbaik dengan harga spesial.',
                style: TextStyle(fontSize: 10, color: AppColors.textMuted),
              ),
            ],
          ),
          bottom: const TabBar(
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.textMuted,
            indicatorColor: AppColors.primary,
            tabs: [
              Tab(text: 'Semua'),
              Tab(text: 'Makanan'),
              Tab(text: 'Minuman'),
              Tab(text: 'Terbatas'),
            ],
          ),
        ),
        body: Column(
          children: [
            // Loyalty Reward Card
            Container(
              margin: const EdgeInsets.all(16),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF3E1F00), Color(0xFF6D4C41)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.coffee_rounded, color: AppColors.accentGold, size: 18),
                      SizedBox(width: 6),
                      Text(
                        'Loyalty Reward',
                        style: TextStyle(
                          color: AppColors.accentGold,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'Plus Jakarta Sans',
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  const Text(
                    'Beli 5 kopi, dapat 1 gratis!',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Progress row of cups
                  Row(
                    children: List.generate(5, (idx) {
                      final filled = idx < 3; // 3 of 5 cups collected
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: Icon(
                          Icons.coffee_rounded,
                          size: 28,
                          color: filled ? AppColors.accentGold : Colors.white.withOpacity(0.3),
                        ),
                      );
                    }),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    '3/5 Cup · 2 lagi untuk kopi gratis!',
                    style: TextStyle(
                      color: AppColors.creamDark,
                      fontSize: 12,
                      fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                ],
              ),
            ),

            // Voucher List
            Expanded(
              child: TabBarView(
                children: [
                  _VoucherList(vouchers: vouchers, filterType: null, onApply: (v) => _applyVoucher(context, v, cart)),
                  _VoucherList(vouchers: vouchers, filterType: 'Makanan', onApply: (v) => _applyVoucher(context, v, cart)),
                  _VoucherList(vouchers: vouchers, filterType: 'Minuman', onApply: (v) => _applyVoucher(context, v, cart)),
                  _VoucherList(vouchers: vouchers, filterType: 'Terbatas', onApply: (v) => _applyVoucher(context, v, cart)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _applyVoucher(BuildContext context, Map v, CartProvider cart) {
    cart.applyVoucher(v['code'] as String, v['title'] as String, v['discount'] as int);
    Navigator.pop(context);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text("Voucher '${v['title']}' berhasil digunakan!"),
        backgroundColor: AppColors.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }
}

class _VoucherList extends StatelessWidget {
  final List vouchers;
  final String? filterType;
  final Function(Map) onApply;

  const _VoucherList({required this.vouchers, required this.filterType, required this.onApply});

  @override
  Widget build(BuildContext context) {
    final filtered = filterType == null
        ? vouchers
        : vouchers.where((v) => v['type'] == filterType).toList();

    if (filtered.isEmpty) {
      return const Center(
        child: Text(
          'Tidak ada voucher untuk kategori ini.',
          style: TextStyle(color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      itemCount: filtered.length,
      itemBuilder: (ctx, idx) {
        final v = filtered[idx];
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: AppColors.primary.withOpacity(0.07),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: Row(
              children: [
                // Left image section
                SizedBox(
                  width: 100,
                  height: 100,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      Image.network(v['image'] as String, fit: BoxFit.cover),
                      Container(color: (v['color'] as Color).withOpacity(0.75)),
                      Center(
                        child: Text(
                          v['badge'] as String,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 11,
                            fontFamily: 'Plus Jakarta Sans',
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                    ],
                  ),
                ),
                // Right info
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          v['title'] as String,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            color: AppColors.textDark,
                            fontFamily: 'Plus Jakarta Sans',
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          v['description'] as String,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.textMuted,
                            fontFamily: 'Plus Jakarta Sans',
                          ),
                        ),
                        const SizedBox(height: 10),
                        Align(
                          alignment: Alignment.centerRight,
                          child: ElevatedButton(
                            onPressed: () => onApply(v),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: AppColors.cream,
                              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 6),
                              minimumSize: Size.zero,
                              textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                            child: const Text('Gunakan'),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
