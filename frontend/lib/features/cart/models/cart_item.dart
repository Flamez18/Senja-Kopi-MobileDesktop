import '../../../core/models/product.dart';

class CartItem {
  final Product product;
  int quantity;
  final String temperature; // "Panas" atau "Dingin"
  final String? customNotes; // "Less Sugar", "Hangatkan", dll.

  CartItem({
    required this.product,
    this.quantity = 1,
    required this.temperature,
    this.customNotes,
  });

  // Calculate subtotal
  int get subtotal => product.price * quantity;

  // Key to identify unique item customization in cart
  String get uniqueKey => '${product.id}-$temperature-$customNotes';

  Map<String, dynamic> toJson() {
    return {
      'product': product.toJson(),
      'quantity': quantity,
      'temperature': temperature,
      'custom_notes': customNotes,
    };
  }

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      product: Product.fromJson(json['product']),
      quantity: json['quantity'] as int,
      temperature: json['temperature'] as String? ?? 'Panas',
      customNotes: json['custom_notes'] as String?,
    );
  }
}
