import 'package:flutter/material.dart';
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/models/order.dart';

class OrderProvider extends ChangeNotifier {
  List<Order> _orders = [];
  Order? _currentOrder;
  bool _isLoading = false;
  String? _errorMessage;

  List<Order> get orders => _orders;
  Order? get currentOrder => _currentOrder;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchOrders() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiClient.instance.get(ApiEndpoints.orders);
      if (response.data['success'] == true) {
        final List list = response.data['data'];
        _orders = list.map((o) => Order.fromJson(o)).toList();
      } else {
        _errorMessage = response.data['message'] ?? 'Gagal mengambil data pesanan';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (_) {
      _errorMessage = 'Terjadi kesalahan jaringan';
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchOrderDetail(int orderId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiClient.instance.get(ApiEndpoints.orderDetail(orderId));
      if (response.data['success'] == true) {
        _currentOrder = Order.fromJson(response.data['data']);
      } else {
        _errorMessage = response.data['message'] ?? 'Gagal mengambil detail pesanan';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (_) {
      _errorMessage = 'Terjadi kesalahan jaringan';
    }

    _isLoading = false;
    notifyListeners();
  }

  // Status helpers
  static String statusLabel(String status) {
    switch (status) {
      case 'waiting_payment': return 'Menunggu Bayar';
      case 'processing': return 'Diproses';
      case 'making': return 'Sedang Dibuat';
      case 'ready': return 'Siap Diambil';
      case 'completed': return 'Selesai';
      case 'cancelled': return 'Dibatalkan';
      default: return status;
    }
  }

  static Color statusColor(String status) {
    switch (status) {
      case 'waiting_payment': return const Color(0xFFF57C00);
      case 'processing': return const Color(0xFF1565C0);
      case 'making': return const Color(0xFF6A1B9A);
      case 'ready': return const Color(0xFF2E7D32);
      case 'completed': return const Color(0xFF388E3C);
      case 'cancelled': return const Color(0xFFC62828);
      default: return const Color(0xFF9E9E9E);
    }
  }
}
