import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../api/api_client.dart';
import '../api/api_endpoints.dart';

// Handler untuk notifikasi background (harus top-level function)
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Tidak perlu inisialisasi Firebase di sini karena sudah diinit di main()
}

class FcmService {
  static final FcmService _instance = FcmService._internal();
  factory FcmService() => _instance;
  FcmService._internal();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  // Channel untuk Android
  static const AndroidNotificationChannel _channel = AndroidNotificationChannel(
    'kopi_senja_orders',
    'Notifikasi Pesanan',
    description: 'Notifikasi status pesanan Kopi Senja',
    importance: Importance.high,
    playSound: true,
  );

  // Callback navigasi (diisi dari main.dart)
  static Function(String? orderId)? onNotificationTap;

  /// Inisialisasi FCM - dipanggil dari main()
  Future<void> initialize() async {
    // 1. Setup background handler
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    // 2. Minta izin notifikasi (Android 13+)
    await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // 3. Setup local notifications channel
    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);

    // 4. Inisialisasi plugin local notifications
    const initializationSettings = InitializationSettings(
      android: AndroidInitializationSettings('@mipmap/ic_launcher'),
    );
    await _localNotifications.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: (details) {
        // Tap notifikasi saat app foreground/background
        final payload = details.payload;
        if (payload != null) {
          try {
            final data = json.decode(payload);
            onNotificationTap?.call(data['order_id']?.toString());
          } catch (_) {}
        }
      },
    );

    // 5. Handle notifikasi saat app foreground
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      _showLocalNotification(message);
    });

    // 6. Handle tap notifikasi saat app background (tapi tidak terminated)
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      final orderId = message.data['order_id']?.toString();
      onNotificationTap?.call(orderId);
    });

    // 7. Handle tap notifikasi saat app terminated
    final initialMessage = await _messaging.getInitialMessage();
    if (initialMessage != null) {
      final orderId = initialMessage.data['order_id']?.toString();
      // Delay kecil agar navigator sudah siap
      Future.delayed(const Duration(seconds: 1), () {
        onNotificationTap?.call(orderId);
      });
    }
  }

  /// Ambil FCM token device ini
  Future<String?> getToken() async {
    try {
      return await _messaging.getToken();
    } catch (e) {
      debugPrint('FCM getToken error: $e');
      return null;
    }
  }

  /// Upload FCM token ke backend setelah login
  Future<void> uploadTokenToServer() async {
    try {
      final token = await getToken();
      if (token == null) return;

      await ApiClient.instance.put(
        ApiEndpoints.updateFcmToken,
        data: {'fcm_token': token},
      );
      debugPrint('FCM token uploaded: ${token.substring(0, 20)}...');
    } catch (e) {
      // Jangan crash app jika gagal upload token
      debugPrint('FCM uploadToken error: $e');
    }
  }

  /// Tampilkan notifikasi lokal saat app sedang foreground
  void _showLocalNotification(RemoteMessage message) {
    final notification = message.notification;
    if (notification == null) return;

    final androidDetails = AndroidNotificationDetails(
      _channel.id,
      _channel.name,
      channelDescription: _channel.description,
      importance: Importance.high,
      priority: Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(android: androidDetails),
      payload: json.encode(message.data),
    );
  }
}
