class Branch {
  final int id;
  final String name;
  final String address;
  final String city;
  final String? phone;
  final String openTime;
  final String closeTime;
  final bool isActive;

  Branch({
    required this.id,
    required this.name,
    required this.address,
    required this.city,
    this.phone,
    required this.openTime,
    required this.closeTime,
    required this.isActive,
  });

  factory Branch.fromJson(Map<String, dynamic> json) {
    return Branch(
      id: json['id'] as int,
      name: json['name'] as String,
      address: json['address'] as String,
      city: json['city'] as String,
      phone: json['phone'] as String?,
      openTime: json['open_time'] as String,
      closeTime: json['close_time'] as String,
      isActive: json['is_active'] as bool? ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'address': address,
      'city': city,
      'phone': phone,
      'open_time': openTime,
      'close_time': closeTime,
      'is_active': isActive,
    };
  }
}
