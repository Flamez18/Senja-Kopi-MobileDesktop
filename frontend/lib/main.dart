import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter/services.dart';

// Core
import 'core/theme/app_theme.dart';
import 'core/services/storage_service.dart';

// Providers
import 'features/auth/providers/auth_provider.dart';
import 'features/branch/providers/branch_provider.dart';
import 'features/cart/providers/cart_provider.dart';
import 'features/home/providers/home_provider.dart';
import 'features/order/providers/order_provider.dart';
import 'features/checkout/providers/checkout_provider.dart';

// Screens
import 'features/auth/presentation/splash_screen.dart';
import 'features/auth/presentation/login_screen.dart';
import 'features/auth/presentation/register_screen.dart';
import 'features/branch/presentation/branch_picker_screen.dart';
import 'features/shell/main_shell.dart';
import 'features/product/presentation/product_detail_screen.dart';
import 'features/cart/presentation/cart_screen.dart';
import 'features/voucher/presentation/voucher_screen.dart';
import 'features/checkout/presentation/checkout_screen.dart';
import 'features/payment/presentation/payment_qris_screen.dart';
import 'features/payment/presentation/transfer_bank_screen.dart';
import 'features/payment/presentation/payment_result_screens.dart';
import 'features/order/presentation/order_history_screen.dart';
import 'features/order/presentation/order_detail_screen.dart';
import 'features/profile/presentation/edit_profile_screen.dart';
import 'features/profile/presentation/payment_methods_screen.dart';
import 'features/favorites/presentation/favorites_screen.dart';

// Models
import 'core/models/product.dart';
import 'core/models/order.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await StorageService.init();
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ),
  );
  SystemChrome.setPreferredOrientations([DeviceOrientation.portraitUp, DeviceOrientation.portraitDown]);

  runApp(const KopiSenjaApp());
}

class KopiSenjaApp extends StatelessWidget {
  const KopiSenjaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => BranchProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => HomeProvider()),
        ChangeNotifierProvider(create: (_) => OrderProvider()),
        ChangeNotifierProvider(create: (_) => CheckoutProvider()),
      ],
      child: MaterialApp(
        title: 'Kopi Senja',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        initialRoute: '/splash',
        onGenerateRoute: _generateRoute,
      ),
    );
  }

  static Route<dynamic>? _generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case '/splash':
        return _fade(const SplashScreen());

      case '/login':
        return _slide(const LoginScreen());

      case '/register':
        return _slide(const RegisterScreen());

      case '/branch-picker':
        return _slide(const BranchPickerScreen());

      case '/home':
        return _fade(const MainShell());

      case '/product':
        final product = settings.arguments as Product;
        return _slide(ProductDetailScreen(product: product));

      case '/cart':
        return _slide(const CartScreen());

      case '/vouchers':
        return _slide(const VoucherScreen());

      case '/checkout':
        return _slide(const CheckoutScreen());

      case '/payment/qris':
        final order = settings.arguments as Order;
        return _slide(PaymentQrisScreen(order: order));

      case '/payment/transfer':
        final order = settings.arguments as Order;
        return _slide(TransferBankScreen(order: order));

      case '/payment/success':
        final order = settings.arguments as Order;
        return _fade(PaymentSuccessScreen(order: order));

      case '/payment/failure':
        final order = settings.arguments as Order?;
        return _fade(PaymentFailureScreen(order: order));

      case '/orders':
        return _slide(const OrderHistoryScreen());

      case '/order-detail':
        final order = settings.arguments as Order;
        return _slide(OrderDetailScreen(order: order));

      case '/profile/edit':
        return _slide(const EditProfileScreen());

      case '/profile/payment-methods':
        return _slide(const PaymentMethodsScreen());

      case '/favorites':
        return _slide(const FavoritesScreen());

      default:
        return _fade(const MainShell());
    }
  }

  static Route<T> _fade<T>(Widget page) {
    return PageRouteBuilder<T>(
      settings: RouteSettings(name: page.runtimeType.toString()),
      pageBuilder: (_, __, ___) => page,
      transitionsBuilder: (_, animation, __, child) {
        return FadeTransition(opacity: animation, child: child);
      },
      transitionDuration: const Duration(milliseconds: 300),
    );
  }

  static Route<T> _slide<T>(Widget page) {
    return PageRouteBuilder<T>(
      pageBuilder: (_, __, ___) => page,
      transitionsBuilder: (_, animation, __, child) {
        return SlideTransition(
          position: Tween<Offset>(begin: const Offset(1, 0), end: Offset.zero)
              .animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
          child: child,
        );
      },
      transitionDuration: const Duration(milliseconds: 280),
    );
  }
}
