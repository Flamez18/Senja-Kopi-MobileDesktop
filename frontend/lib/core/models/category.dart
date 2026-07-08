class Category {
  final int id;
  final String name;
  final String? iconUrl;
  final int sortOrder;

  Category({
    required this.id,
    required this.name,
    this.iconUrl,
    required this.sortOrder,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] as int,
      name: json['name'] as String,
      iconUrl: json['icon_url'] as String?,
      sortOrder: json['sort_order'] as int? ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'icon_url': iconUrl,
      'sort_order': sortOrder,
    };
  }
}
