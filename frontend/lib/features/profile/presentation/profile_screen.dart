import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../auth/providers/auth_provider.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      backgroundColor: AppColors.bgBody,
      appBar: AppBar(
        title: const Text('Profil Saya'),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings_outlined, color: AppColors.textMuted),
            onPressed: () {},
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Profile header
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              color: Colors.white,
              child: Column(
                children: [
                  // Avatar
                  CircleAvatar(
                    radius: 44,
                    backgroundColor: AppColors.creamDark,
                    backgroundImage: user?.avatarUrl != null
                        ? NetworkImage(user!.avatarUrl!)
                        : null,
                    child: user?.avatarUrl == null
                        ? const Icon(Icons.person_rounded, size: 44, color: AppColors.primary)
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    user?.name ?? 'Pengguna',
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textDark,
                      fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    user?.email ?? '',
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppColors.textMuted,
                      fontFamily: 'Plus Jakarta Sans',
                    ),
                  ),
                ],
              ),
            ),

            // Senja Rewards Card
            Padding(
              padding: const EdgeInsets.all(16),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF3E1F00), Color(0xFF6D4C41)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withOpacity(0.3),
                      blurRadius: 12,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Icon(Icons.star_rounded, color: AppColors.accentGold, size: 18),
                                SizedBox(width: 4),
                                Text(
                                  'Gold Member',
                                  style: TextStyle(
                                    color: AppColors.accentGold,
                                    fontWeight: FontWeight.bold,
                                    fontFamily: 'Plus Jakarta Sans',
                                  ),
                                ),
                              ],
                            ),
                            SizedBox(height: 4),
                            Text(
                              '250',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 32,
                                fontWeight: FontWeight.w900,
                                fontFamily: 'Plus Jakarta Sans',
                              ),
                            ),
                            Text(
                              'Poin Tersedia',
                              style: TextStyle(color: AppColors.creamDark, fontSize: 12, fontFamily: 'Plus Jakarta Sans'),
                            ),
                          ],
                        ),
                        const Icon(Icons.coffee_rounded, color: AppColors.accentGold, size: 48),
                      ],
                    ),
                    const SizedBox(height: 16),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: LinearProgressIndicator(
                        value: 250 / 400,
                        backgroundColor: Colors.white.withOpacity(0.2),
                        valueColor: const AlwaysStoppedAnimation<Color>(AppColors.accentGold),
                        minHeight: 8,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      '150 poin lagi untuk Platinum 🎉',
                      style: TextStyle(color: AppColors.creamDark, fontSize: 12, fontFamily: 'Plus Jakarta Sans'),
                    ),
                  ],
                ),
              ),
            ),

            // Menu Items
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  _MenuItem(
                    icon: Icons.person_outline_rounded,
                    label: 'Edit Profil',
                    onTap: () => Navigator.pushNamed(context, '/profile/edit'),
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.creamDark),
                  _MenuItem(
                    icon: Icons.account_balance_wallet_outlined,
                    label: 'Metode Pembayaran',
                    onTap: () => Navigator.pushNamed(context, '/profile/payment-methods'),
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.creamDark),
                  _MenuItem(
                    icon: Icons.favorite_outline_rounded,
                    label: 'Cabang Favorit',
                    onTap: () => Navigator.pushNamed(context, '/favorites'),
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.creamDark),
                  _MenuItem(
                    icon: Icons.local_offer_outlined,
                    label: 'Voucher Saya',
                    onTap: () => Navigator.pushNamed(context, '/vouchers'),
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.creamDark),
                  _MenuItem(
                    icon: Icons.settings_outlined,
                    label: 'Pengaturan',
                    onTap: () {},
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Help and Logout
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  _MenuItem(
                    icon: Icons.help_outline_rounded,
                    label: 'Pusat Bantuan',
                    iconColor: AppColors.info,
                    onTap: () {},
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.creamDark),
                  _MenuItem(
                    icon: Icons.logout_rounded,
                    label: 'Keluar',
                    iconColor: AppColors.danger,
                    labelColor: AppColors.danger,
                    showChevron: false,
                    onTap: () async {
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: const Text('Keluar dari Akun?'),
                          content: const Text('Anda akan keluar dari akun Kopi Senja.'),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
                            TextButton(
                              onPressed: () => Navigator.pop(ctx, true),
                              child: const Text('Keluar', style: TextStyle(color: AppColors.danger)),
                            ),
                          ],
                        ),
                      );
                      if (confirm == true && context.mounted) {
                        final authProvider = Provider.of<AuthProvider>(context, listen: false);
                        await authProvider.logout();
                        if (context.mounted) {
                          Navigator.pushNamedAndRemoveUntil(context, '/login', (r) => false);
                        }
                      }
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Versi 1.0.0 · Kopi Senja Coffee Shop',
              style: TextStyle(fontSize: 11, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'),
            ),
            const SizedBox(height: 80),
          ],
        ),
      ),
    );
  }
}

class _MenuItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color? iconColor;
  final Color? labelColor;
  final bool showChevron;

  const _MenuItem({
    required this.icon,
    required this.label,
    required this.onTap,
    this.iconColor,
    this.labelColor,
    this.showChevron = true,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: (iconColor ?? AppColors.primary).withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: iconColor ?? AppColors.primary, size: 20),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  fontSize: 14,
                  color: labelColor ?? AppColors.textDark,
                  fontWeight: FontWeight.w500,
                  fontFamily: 'Plus Jakarta Sans',
                ),
              ),
            ),
            if (showChevron) const Icon(Icons.chevron_right_rounded, color: AppColors.textMuted, size: 20),
          ],
        ),
      ),
    );
  }
}
