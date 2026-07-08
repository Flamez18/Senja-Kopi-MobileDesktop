import 'package:flutter/material.dart';
import '../models/cart_item.dart';
import '../../../core/models/product.dart';

class CartProvider extends ChangeNotifier {
  final List<CartItem> _items = [];
  String? _appliedVoucherCode;
  String? _appliedVoucherTitle;
  int _voucherDiscount = 0;
  static const int serviceFee = 2000;

  List<CartItem> get items => List.unmodifiable(_items);
  String? get appliedVoucherCode => _appliedVoucherCode;
  String? get appliedVoucherTitle => _appliedVoucherTitle;
  int get voucherDiscount => _voucherDiscount;

  bool get isEmpty => _items.isEmpty;
  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  int get subtotal => _items.fold(0, (sum, item) => sum + item.subtotal);
  int get total => subtotal + serviceFee - _voucherDiscount;

  // Add item to cart (or increment quantity if same unique key)
  void addItem(Product product, String temperature, {String? customNotes}) {
    final key = '${product.id}-$temperature-$customNotes';
    final existingIndex = _items.indexWhere((item) => item.uniqueKey == key);

    if (existingIndex >= 0) {
      _items[existingIndex].quantity++;
    } else {
      _items.add(CartItem(
        product: product,
        quantity: 1,
        temperature: temperature,
        customNotes: customNotes,
      ));
    }
    notifyListeners();
  }

  void incrementItem(String uniqueKey) {
    final index = _items.indexWhere((item) => item.uniqueKey == uniqueKey);
    if (index >= 0) {
      _items[index].quantity++;
      notifyListeners();
    }
  }

  void decrementItem(String uniqueKey) {
    final index = _items.indexWhere((item) => item.uniqueKey == uniqueKey);
    if (index >= 0) {
      if (_items[index].quantity <= 1) {
        _items.removeAt(index);
      } else {
        _items[index].quantity--;
      }
      notifyListeners();
    }
  }

  void removeItem(String uniqueKey) {
    _items.removeWhere((item) => item.uniqueKey == uniqueKey);
    notifyListeners();
  }

  void clearCart() {
    _items.clear();
    _appliedVoucherCode = null;
    _appliedVoucherTitle = null;
    _voucherDiscount = 0;
    notifyListeners();
  }

  // Apply a voucher
  void applyVoucher(String code, String title, int discount) {
    _appliedVoucherCode = code;
    _appliedVoucherTitle = title;
    _voucherDiscount = discount;
    notifyListeners();
  }

  void removeVoucher() {
    _appliedVoucherCode = null;
    _appliedVoucherTitle = null;
    _voucherDiscount = 0;
    notifyListeners();
  }
}
