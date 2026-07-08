import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/models/branch.dart';
import '../../branch/providers/branch_provider.dart';
import '../../cart/providers/cart_provider.dart';

class BranchPickerScreen extends StatefulWidget {
  const BranchPickerScreen({super.key});

  @override
  State<BranchPickerScreen> createState() => _BranchPickerScreenState();
}

class _BranchPickerScreenState extends State<BranchPickerScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<BranchProvider>(context, listen: false).fetchBranches();
    });
  }

  void _selectBranch(Branch branch) async {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final branchProvider = Provider.of<BranchProvider>(context, listen: false);

    // Jika cart berisi item, tampilkan dialog konfirmasi
    if (!cartProvider.isEmpty &&
        branchProvider.selectedBranch != null &&
        branchProvider.selectedBranch!.id != branch.id) {
      final confirm = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text(
            'Ganti Cabang?',
            style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary),
          ),
          content: const Text(
            'Keranjang Anda saat ini akan dikosongkan jika berpindah ke cabang lain. Lanjutkan?',
            style: TextStyle(color: AppColors.textMuted),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pop(ctx, true),
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
              child: const Text('Ya, Ganti', style: TextStyle(color: AppColors.cream)),
            ),
          ],
        ),
      );
      if (confirm != true) return;
      cartProvider.clearCart();
    }

    await branchProvider.selectBranch(branch);

    if (mounted) {
      Navigator.pushReplacementNamed(context, '/home');
    }
  }

  @override
  Widget build(BuildContext context) {
    final branchProvider = context.watch<BranchProvider>();

    return Scaffold(
      body: Stack(
        children: [
          // Background image
          Container(
            decoration: const BoxDecoration(
              image: DecorationImage(
                image: NetworkImage(
                  'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=800&q=80',
                ),
                fit: BoxFit.cover,
              ),
            ),
          ),
          Container(color: Colors.black.withOpacity(0.72)),

          SafeArea(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 32),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.coffee_rounded, color: AppColors.accentGold, size: 28),
                          SizedBox(width: 10),
                          Text(
                            'Kopi Senja',
                            style: TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.w800,
                              color: AppColors.cream,
                              fontFamily: 'Plus Jakarta Sans',
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: 16),
                      Text(
                        'Pilih Cabang',
                        style: TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                          fontFamily: 'Plus Jakarta Sans',
                        ),
                      ),
                      SizedBox(height: 6),
                      Text(
                        'Pilih cabang terdekat untuk memulai pengalaman Kopi Senja Anda.',
                        style: TextStyle(
                          fontSize: 14,
                          color: AppColors.creamDark,
                          fontFamily: 'Plus Jakarta Sans',
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 32),

                Expanded(
                  child: branchProvider.isLoading
                      ? const Center(child: CircularProgressIndicator(color: AppColors.accentGold))
                      : branchProvider.errorMessage != null
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.wifi_off_rounded, color: AppColors.cream, size: 48),
                                  const SizedBox(height: 12),
                                  Text(
                                    branchProvider.errorMessage!,
                                    style: const TextStyle(color: AppColors.cream),
                                    textAlign: TextAlign.center,
                                  ),
                                  const SizedBox(height: 16),
                                  ElevatedButton(
                                    onPressed: () => branchProvider.fetchBranches(),
                                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.accentGold),
                                    child: const Text('Coba Lagi', style: TextStyle(color: AppColors.primary)),
                                  ),
                                ],
                              ),
                            )
                          : ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
                              itemCount: branchProvider.branches.length,
                              itemBuilder: (context, index) {
                                final branch = branchProvider.branches[index];
                                return _BranchCard(
                                  branch: branch,
                                  onTap: () => _selectBranch(branch),
                                );
                              },
                            ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _BranchCard extends StatelessWidget {
  final Branch branch;
  final VoidCallback onTap;

  const _BranchCard({required this.branch, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.92),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.15),
            blurRadius: 12,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.creamDark,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.store_rounded, color: AppColors.primary, size: 28),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      branch.name,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: AppColors.primary,
                        fontFamily: 'Plus Jakarta Sans',
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      branch.address,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textMuted,
                        fontFamily: 'Plus Jakarta Sans',
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.access_time_rounded, color: AppColors.accentGold, size: 13),
                        const SizedBox(width: 4),
                        Text(
                          '${branch.openTime.substring(0, 5)} – ${branch.closeTime.substring(0, 5)}',
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.textMuted,
                            fontFamily: 'Plus Jakarta Sans',
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
            ],
          ),
        ),
      ),
    );
  }
}
