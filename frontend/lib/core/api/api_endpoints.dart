class ApiEndpoints {
  // Gunakan IP 10.0.2.2 jika dijalankan di emulator Android standar untuk mengakses localhost OS utama
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1';
  
  // Alternative (jika device fisik / tunnel ngrok):
  // static const String baseUrl = 'https://xxxx.ngrok-free.app/api/v1';

  // Auth endpoints
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String logout = '/auth/logout';
  static const String me = '/auth/me';
  static const String updateProfile = '/auth/profile';
  static const String updateAvatar = '/auth/avatar';

  // Branches endpoints
  static const String branches = '/branches';

  // Catalog endpoints
  static const String banners = '/banners';
  static const String categories = '/categories';
  static const String products = '/products';

  // Orders endpoints
  static const String orders = '/orders';
  static String orderDetail(int id) => '/orders/$id';
  static String cancelOrder(int id) => '/orders/$id/cancel';

  // Payment endpoints
  static String initiatePayment(int id) => '/orders/$id/payment/initiate';
  static String paymentStatus(int id) => '/orders/$id/payment/status';
}
