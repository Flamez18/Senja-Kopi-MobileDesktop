import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/models/order.dart';
import '../../cart/providers/cart_provider.dart';
import '../../branch/providers/branch_provider.dart';

class CheckoutProvider extends ChangeNotifier {
  bool _isLoading = false;
  String? _errorMessage;
  Order? _createdOrder;
  String _selectedPaymentMethod = 'qris'; // 'qris', 'ewallet', 'transfer'
  String _notes = '';

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  Order? get createdOrder => _createdOrder;
  String get selectedPaymentMethod => _selectedPaymentMethod;
  String get notes => _notes;

  void setPaymentMethod(String method) {
    _selectedPaymentMethod = method;
    notifyListeners();
  }

  void setNotes(String notes) {
    _notes = notes;
    notifyListeners();
  }

  // Create order and initiate payment
  Future<Order?> placeOrder(BuildContext context) async {
    final cart = Provider.of<CartProvider>(context, listen: false);
    final branch = Provider.of<BranchProvider>(context, listen: false).selectedBranch;

    if (branch == null || cart.isEmpty) return null;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // Build order items payload
      final orderItems = cart.items.map((item) => {
        'product_id': item.product.id,
        'quantity': item.quantity,
      }).toList();

      final response = await ApiClient.instance.post(
        ApiEndpoints.orders,
        data: {
          'branch_id': branch.id,
          'payment_method': _selectedPaymentMethod,
          'notes': _notes.isNotEmpty ? _notes : null,
          'items': orderItems,
          'voucher_discount': cart.voucherDiscount > 0 ? cart.voucherDiscount : null,
        },
      );

      if (response.data['success'] == true) {
        _createdOrder = Order.fromJson(response.data['data']);
        _isLoading = false;
        notifyListeners();
        cart.clearCart();
        return _createdOrder;
      } else {
        _errorMessage = response.data['message'] ?? 'Gagal membuat pesanan';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem';
    }

    _isLoading = false;
    notifyListeners();
    return null;
  }
}
