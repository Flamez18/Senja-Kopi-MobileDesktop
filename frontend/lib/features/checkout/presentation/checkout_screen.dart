import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../cart/providers/cart_provider.dart';
import '../../branch/providers/branch_provider.dart';
import '../providers/checkout_provider.dart';
import '../../../core/models/order.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final branch = context.watch<BranchProvider>().selectedBranch;
    final checkout = context.watch<CheckoutProvider>();

    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(title: const Text('Checkout')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Branch info
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14)),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: AppColors.creamDark, borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.store_rounded, color: AppColors.primary, size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          branch?.name ?? 'Pilih Cabang',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontFamily: 'Plus Jakarta Sans'),
                        ),
                        if (branch != null)
                          Text(
                            branch.address,
                            style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Order summary header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Ringkasan Pesanan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans')),
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('+ Tambah Menu', style: TextStyle(color: AppColors.primary, fontSize: 12)),
                ),
              ],
            ),
            const SizedBox(height: 8),

            // Items
            Container(
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14)),
              child: Column(
                children: cart.items.map((item) {
                  return ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                    leading: ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: item.product.imageUrl != null
                          ? CachedNetworkImage(imageUrl: item.product.imageUrl!, width: 46, height: 46, fit: BoxFit.cover, errorWidget: (_, __, ___) => Container(width: 46, height: 46, color: AppColors.creamDark))
                          : Container(width: 46, height: 46, color: AppColors.creamDark),
                    ),
                    title: Text(item.product.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, fontFamily: 'Plus Jakarta Sans')),
                    subtitle: Text('${item.temperature} · x${item.quantity}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                    trailing: Text(CurrencyFormatter.toRupiah(item.subtotal), style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontSize: 13, fontFamily: 'Plus Jakarta Sans')),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 16),

            // Notes
            const Text('Catatan Pesanan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans')),
            const SizedBox(height: 8),
            TextField(
              onChanged: (val) => checkout.setNotes(val),
              maxLines: 2,
              decoration: InputDecoration(
                hintText: 'Es sedikit, kurang manis, dll...',
                prefixIcon: const Padding(
                  padding: EdgeInsets.only(top: 14, left: 12, right: 8),
                  child: Icon(Icons.notes_rounded, color: AppColors.textMuted, size: 20),
                ),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.creamDark)),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.creamDark)),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
              ),
            ),
            const SizedBox(height: 16),

            // Payment Method
            const Text('Metode Pembayaran', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans')),
            const SizedBox(height: 10),
            Row(
              children: [
                _PaymentMethodChip(label: 'QRIS', icon: Icons.qr_code_2_rounded, value: 'qris', selectedValue: checkout.selectedPaymentMethod, onTap: () => checkout.setPaymentMethod('qris')),
                const SizedBox(width: 10),
                _PaymentMethodChip(label: 'E-Wallet', icon: Icons.account_balance_wallet_rounded, value: 'ewallet', selectedValue: checkout.selectedPaymentMethod, onTap: () => checkout.setPaymentMethod('ewallet')),
                const SizedBox(width: 10),
                _PaymentMethodChip(label: 'Transfer', icon: Icons.account_balance_rounded, value: 'transfer', selectedValue: checkout.selectedPaymentMethod, onTap: () => checkout.setPaymentMethod('transfer')),
              ],
            ),
            const SizedBox(height: 16),

            // Price summary
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14)),
              child: Column(
                children: [
                  _Row('Subtotal', CurrencyFormatter.toRupiah(cart.subtotal)),
                  const SizedBox(height: 6),
                  _Row('Biaya Layanan', CurrencyFormatter.toRupiah(CartProvider.serviceFee)),
                  if (cart.voucherDiscount > 0) ...[
                    const SizedBox(height: 6),
                    _Row('Diskon Voucher', '- ${CurrencyFormatter.toRupiah(cart.voucherDiscount)}', valueColor: AppColors.success),
                  ],
                  const Divider(height: 20, color: AppColors.creamDark),
                  _Row('Total Pembayaran', CurrencyFormatter.toRupiah(cart.total), isBold: true),
                ],
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
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Pesanan akan siap diambil di cabang setelah pembayaran dikonfirmasi.',
              style: TextStyle(fontSize: 11, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            checkout.isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : ElevatedButton(
                    onPressed: () => _placeOrder(context, checkout),
                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: AppColors.cream),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: const [
                        Text('Konfirmasi Pesanan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, fontFamily: 'Plus Jakarta Sans')),
                        SizedBox(width: 8),
                        Icon(Icons.arrow_forward_rounded, size: 18),
                      ],
                    ),
                  ),
          ],
        ),
      ),
    );
  }

  Future<void> _placeOrder(BuildContext context, CheckoutProvider checkout) async {
    final order = await checkout.placeOrder(context);
    if (!mounted) return;

    if (order != null) {
      _navigateToPayment(context, order, checkout.selectedPaymentMethod);
    } else if (checkout.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(checkout.errorMessage!),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  void _navigateToPayment(BuildContext context, Order order, String method) {
    if (method == 'transfer') {
      Navigator.pushNamedAndRemoveUntil(
        context, '/payment/transfer',
        (r) => r.settings.name == '/home',
        arguments: order,
      );
    } else {
      Navigator.pushNamedAndRemoveUntil(
        context, '/payment/qris',
        (r) => r.settings.name == '/home',
        arguments: order,
      );
    }
  }
}

class _PaymentMethodChip extends StatelessWidget {
  final String label;
  final IconData icon;
  final String value;
  final String selectedValue;
  final VoidCallback onTap;

  const _PaymentMethodChip({required this.label, required this.icon, required this.value, required this.selectedValue, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final isSelected = value == selectedValue;
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary.withOpacity(0.08) : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: isSelected ? AppColors.primary : AppColors.creamDark, width: isSelected ? 2 : 1),
          ),
          child: Column(
            children: [
              Icon(icon, color: isSelected ? AppColors.primary : AppColors.textMuted, size: 24),
              const SizedBox(height: 4),
              Text(label, style: TextStyle(color: isSelected ? AppColors.primary : AppColors.textMuted, fontWeight: isSelected ? FontWeight.bold : FontWeight.normal, fontSize: 12, fontFamily: 'Plus Jakarta Sans')),
            ],
          ),
        ),
      ),
    );
  }
}

class _Row extends StatelessWidget {
  final String label;
  final String value;
  final bool isBold;
  final Color? valueColor;

  const _Row(this.label, this.value, {this.isBold = false, this.valueColor});

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
