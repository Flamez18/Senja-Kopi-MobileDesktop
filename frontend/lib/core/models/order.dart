import 'branch.dart';
import 'order_item.dart';

class Order {
  final int id;
  final String orderNumber;
  final String orderStatus;
  final String paymentStatus;
  final String paymentMethod;
  final String? notes;
  final int subtotal;
  final int serviceFee;
  final int total;
  final String? paidAt;
  final String? midtransSnapToken;
  final String createdAt;
  final String updatedAt;
  final Branch? branch;
  final List<OrderItem>? items;

  Order({
    required this.id,
    required this.orderNumber,
    required this.orderStatus,
    required this.paymentStatus,
    required this.paymentMethod,
    this.notes,
    required this.subtotal,
    required this.serviceFee,
    required this.total,
    this.paidAt,
    this.midtransSnapToken,
    required this.createdAt,
    required this.updatedAt,
    this.branch,
    this.items,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'] as int,
      orderNumber: json['order_number'] as String,
      orderStatus: json['order_status'] as String,
      paymentStatus: json['payment_status'] as String,
      paymentMethod: json['payment_method'] as String,
      notes: json['notes'] as String?,
      subtotal: json['subtotal'] as int,
      serviceFee: json['service_fee'] as int? ?? 2000,
      total: json['total'] as int,
      paidAt: json['paid_at'] as String?,
      midtransSnapToken: json['midtrans_snap_token'] as String?,
      createdAt: json['created_at'] as String,
      updatedAt: json['updated_at'] as String,
      branch: json['branch'] != null ? Branch.fromJson(json['branch']) : null,
      items: json['items'] != null
          ? (json['items'] as List)
              .map((i) => OrderItem.fromJson(i as Map<String, dynamic>))
              .toList()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'order_number': orderNumber,
      'order_status': orderStatus,
      'payment_status': paymentStatus,
      'payment_method': paymentMethod,
      'notes': notes,
      'subtotal': subtotal,
      'service_fee': serviceFee,
      'total': total,
      'paid_at': paidAt,
      'midtrans_snap_token': midtransSnapToken,
      'created_at': createdAt,
      'updated_at': updatedAt,
      'branch': branch?.toJson(),
      'items': items?.map((i) => i.toJson()).toList(),
    };
  }
}
