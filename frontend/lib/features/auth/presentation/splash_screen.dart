import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../auth/providers/auth_provider.dart';
import '../../branch/providers/branch_provider.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );
    _fadeAnimation = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeIn),
    );
    _scaleAnimation = Tween<double>(begin: 0.8, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeOutBack),
    );
    _controller.forward();

    Timer(const Duration(milliseconds: 2200), () => _navigate());
  }

  Future<void> _navigate() async {
    if (!mounted) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final branch = Provider.of<BranchProvider>(context, listen: false);

    if (!auth.isAuthenticated) {
      Navigator.pushReplacementNamed(context, '/login');
      return;
    }

    // Refresh profile in background
    auth.getProfile();

    // Load saved branch selection
    branch.loadSavedBranch();

    if (!mounted) return;

    if (branch.selectedBranch == null) {
      Navigator.pushReplacementNamed(context, '/branch-picker');
    } else {
      Navigator.pushReplacementNamed(context, '/home');
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF3E1F00), Color(0xFF6D4C41)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: Stack(
          children: [
            // Background pattern dots
            Positioned.fill(
              child: Opacity(
                opacity: 0.05,
                child: GridView.builder(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 8),
                  itemBuilder: (_, __) => const Icon(Icons.circle, size: 4, color: Colors.white),
                  physics: const NeverScrollableScrollPhysics(),
                ),
              ),
            ),

            // Center logo content
            Center(
              child: FadeTransition(
                opacity: _fadeAnimation,
                child: ScaleTransition(
                  scale: _scaleAnimation,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // App icon circle
                      Container(
                        width: 110,
                        height: 110,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: AppColors.cream.withOpacity(0.15),
                          border: Border.all(color: AppColors.accentGold.withOpacity(0.5), width: 2),
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.coffee_rounded,
                            size: 58,
                            color: AppColors.accentGold,
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),

                      // App Name
                      const Text(
                        'Kopi Senja',
                        style: TextStyle(
                          fontSize: 36,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                          fontFamily: 'Plus Jakarta Sans',
                          letterSpacing: -0.5,
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Secangkir kenangan di setiap tegukan',
                        style: TextStyle(
                          fontSize: 13,
                          color: AppColors.accentGold,
                          fontFamily: 'Plus Jakarta Sans',
                          letterSpacing: 0.3,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Bottom loader
            Positioned(
              bottom: 48,
              left: 0,
              right: 0,
              child: FadeTransition(
                opacity: _fadeAnimation,
                child: Column(
                  children: [
                    const SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        color: AppColors.accentGold,
                        strokeWidth: 2,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      '© 2024 Kopi Senja',
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.4),
                        fontSize: 11,
                        fontFamily: 'Plus Jakarta Sans',
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
  }
}
