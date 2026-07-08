import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../models/cart_item.dart';
import '../providers/cart_provider.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(
        title: Row(
          children: [
            const Text('Keranjang'),
            const SizedBox(width: 8),
            if (cart.itemCount > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '${cart.itemCount} item',
                  style: const TextStyle(
                    color: AppColors.cream,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
          ],
        ),
        actions: [
          if (!cart.isEmpty)
            TextButton(
              onPressed: () {
                showDialog(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    title: const Text('Kosongkan Keranjang?'),
                    content: const Text('Semua item akan dihapus.'),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
                      TextButton(
                        onPressed: () {
                          cart.clearCart();
                          Navigator.pop(ctx);
                        },
                        child: const Text('Hapus', style: TextStyle(color: AppColors.danger)),
                      ),
                    ],
                  ),
                );
              },
              child: const Text('Hapus Semua', style: TextStyle(color: AppColors.danger, fontSize: 13)),
            ),
        ],
      ),
      body: cart.isEmpty
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.shopping_bag_outlined, size: 80, color: AppColors.creamDark),
                  SizedBox(height: 16),
                  Text(
                    'Keranjangmu kosong',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textMuted,
                      fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                  SizedBox(height: 8),
                  Text(
                    'Yuk pilih menu favoritmu!',
                    style: TextStyle(color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
                  ),
                ],
              ),
            )
          : Column(
              children: [
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      const Text(
                        'Pesanan Kamu',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textDark,
                          fontFamily: 'Plus Jakarta Sans',
                        ),
                      ),
                      const SizedBox(height: 10),
                      ...cart.items.map((item) => _CartItemCard(item: item, cartProvider: cart)),
                      const SizedBox(height: 12),

                      // Voucher Section
                      GestureDetector(
                        onTap: () => Navigator.pushNamed(context, '/vouchers'),
                        child: cart.appliedVoucherCode != null
                            ? Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  color: AppColors.success.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: AppColors.success.withOpacity(0.3)),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.local_offer_rounded, color: AppColors.success, size: 20),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Text(
                                        "Voucher '${cart.appliedVoucherTitle}' terpasang",
                                        style: const TextStyle(
                                          color: AppColors.success,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 13,
                                          fontFamily: 'Plus Jakarta Sans',
                                        ),
                                      ),
                                    ),
                                    GestureDetector(
                                      onTap: () => cart.removeVoucher(),
                                      child: const Text(
                                        'Hapus',
                                        style: TextStyle(color: AppColors.danger, fontSize: 12, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  ],
                                ),
                              )
                            : Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: AppColors.creamDark),
                                ),
                                child: Row(
                                  children: const [
                                    Icon(Icons.local_offer_outlined, color: AppColors.accentGold, size: 20),
                                    SizedBox(width: 10),
                                    Text('Punya kode promo?', style: TextStyle(color: AppColors.textMuted, fontSize: 13, fontFamily: 'Plus Jakarta Sans')),
                                    Spacer(),
                                    Text('Gunakan', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 13)),
                                    SizedBox(width: 4),
                                    Icon(Icons.chevron_right, color: AppColors.textMuted, size: 18),
                                  ],
                                ),
                              ),
                      ),
                      const SizedBox(height: 16),

                      // Order Summary
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Ringkasan Pesanan',
                              style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark, fontSize: 15, fontFamily: 'Plus Jakarta Sans'),
                            ),
                            const SizedBox(height: 12),
                            _SummaryRow(label: 'Total Item (${cart.itemCount})', value: CurrencyFormatter.toRupiah(cart.subtotal)),
                            const SizedBox(height: 8),
                            _SummaryRow(label: 'Biaya Layanan', value: CurrencyFormatter.toRupiah(CartProvider.serviceFee)),
                            if (cart.voucherDiscount > 0) ...[
                              const SizedBox(height: 8),
                              _SummaryRow(label: 'Diskon Voucher', value: '- ${CurrencyFormatter.toRupiah(cart.voucherDiscount)}', valueColor: AppColors.success),
                            ],
                            const Divider(height: 20, color: AppColors.creamDark),
                            _SummaryRow(label: 'Total Harga', value: CurrencyFormatter.toRupiah(cart.total), isBold: true),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                // Checkout Button
                Container(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.1), blurRadius: 12, offset: const Offset(0, -4))],
                  ),
                  child: ElevatedButton(
                    onPressed: () => Navigator.pushNamed(context, '/checkout'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.cream,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: const [
                        Text('Lanjutkan ke Checkout', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, fontFamily: 'Plus Jakarta Sans')),
                        SizedBox(width: 8),
                        Icon(Icons.arrow_forward_rounded, size: 18),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}

class _CartItemCard extends StatelessWidget {
  final CartItem item;
  final CartProvider cartProvider;

  const _CartItemCard({required this.item, required this.cartProvider});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14)),
      child: Row(
        children: [
          // Product image
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: item.product.imageUrl != null
                ? CachedNetworkImage(
                    imageUrl: item.product.imageUrl!,
                    width: 70, height: 70, fit: BoxFit.cover,
                    errorWidget: (_, __, ___) => Container(width: 70, height: 70, color: AppColors.creamDark, child: const Icon(Icons.coffee_rounded, color: AppColors.textMuted)),
                  )
                : Container(width: 70, height: 70, color: AppColors.creamDark, child: const Icon(Icons.coffee_rounded, color: AppColors.textMuted)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.product.name,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans'),
                ),
                const SizedBox(height: 2),
                Text(item.temperature, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                const SizedBox(height: 6),
                Text(
                  CurrencyFormatter.toRupiah(item.subtotal),
                  style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans'),
                ),
              ],
            ),
          ),
          // Quantity Controls
          Row(
            children: [
              _QtyButton(icon: Icons.remove, onTap: () => cartProvider.decrementItem(item.uniqueKey)),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 10),
                child: Text('${item.quantity}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textDark)),
              ),
              _QtyButton(icon: Icons.add, onTap: () => cartProvider.incrementItem(item.uniqueKey)),
            ],
          ),
        ],
      ),
    );
  }
}

class _QtyButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;

  const _QtyButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final isRemove = icon == Icons.remove;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(
          color: isRemove ? AppColors.creamDark : AppColors.primary,
          shape: BoxShape.circle,
        ),
        child: Icon(icon, size: 16, color: isRemove ? AppColors.primary : Colors.white),
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final bool isBold;
  final Color? valueColor;

  const _SummaryRow({required this.label, required this.value, this.isBold = false, this.valueColor});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(fontSize: 13, color: isBold ? AppColors.textDark : AppColors.textMuted, fontWeight: isBold ? FontWeight.bold : FontWeight.normal, fontFamily: 'Plus Jakarta Sans')),
        Text(value, style: TextStyle(fontSize: 13, color: valueColor ?? (isBold ? AppColors.primary : AppColors.textDark), fontWeight: isBold ? FontWeight.bold : FontWeight.w600, fontFamily: 'Plus Jakarta Sans')),
      ],
    );
  }
}
