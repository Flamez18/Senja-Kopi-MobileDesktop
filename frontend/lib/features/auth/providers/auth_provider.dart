import 'dart:convert';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/models/user.dart';
import '../../../core/services/storage_service.dart';

class AuthProvider extends ChangeNotifier {
  User? _user;
  String? _token;
  bool _isLoading = false;
  String? _errorMessage;

  User? get user => _user;
  String? get token => _token;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _token != null;

  AuthProvider() {
    loadSavedUser();
  }

  // Load user data from SharedPreferences
  void loadSavedUser() {
    _token = StorageService.getToken();
    final userJson = StorageService.getUserJson();
    if (userJson != null) {
      try {
        _user = User.fromJson(json.decode(userJson));
      } catch (_) {
        _user = null;
      }
    }
    notifyListeners();
  }

  // Clear any past errors
  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  // Login
  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiClient.instance.post(
        ApiEndpoints.login,
        data: {
          'email': email,
          'password': password,
        },
      );

      if (response.data['success'] == true) {
        final data = response.data['data'];
        _token = data['access_token'];
        _user = User.fromJson(data['user']);
        
        await StorageService.saveToken(_token!);
        await StorageService.saveUserJson(json.encode(_user!.toJson()));

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.data['message'] ?? 'Login gagal';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  // Register
  Future<bool> register(String name, String email, String phone, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiClient.instance.post(
        ApiEndpoints.register,
        data: {
          'name': name,
          'email': email,
          'phone': phone.isEmpty ? null : phone,
          'password': password,
        },
      );

      if (response.data['success'] == true) {
        final data = response.data['data'];
        _token = data['access_token'];
        _user = User.fromJson(data['user']);
        
        await StorageService.saveToken(_token!);
        await StorageService.saveUserJson(json.encode(_user!.toJson()));

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.data['message'] ?? 'Registrasi gagal';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  // Get/Refresh Profile
  Future<void> getProfile() async {
    try {
      final response = await ApiClient.instance.get(ApiEndpoints.me);
      if (response.data['success'] == true) {
        _user = User.fromJson(response.data['data']);
        await StorageService.saveUserJson(json.encode(_user!.toJson()));
        notifyListeners();
      }
    } catch (_) {
      // Abaikan jika refresh profile gagal di background
    }
  }

  // Update Profile
  Future<bool> updateProfile(String name, String email, String phone) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiClient.instance.put(
        ApiEndpoints.updateProfile,
        data: {
          'name': name,
          'email': email,
          'phone': phone.isEmpty ? null : phone,
        },
      );

      if (response.data['success'] == true) {
        _user = User.fromJson(response.data['data']);
        await StorageService.saveUserJson(json.encode(_user!.toJson()));

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.data['message'] ?? 'Gagal memperbarui profil';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  // Update Profile Avatar
  Future<bool> updateAvatar(File avatarFile) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      String fileName = avatarFile.path.split('/').last;
      FormData formData = FormData.fromMap({
        'avatar': await MultipartFile.fromFile(
          avatarFile.path,
          filename: fileName,
        ),
      });

      final response = await ApiClient.instance.post(
        ApiEndpoints.updateAvatar,
        data: formData,
        options: Options(
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        ),
      );

      if (response.data['success'] == true) {
        _user = User.fromJson(response.data['data']);
        await StorageService.saveUserJson(json.encode(_user!.toJson()));

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.data['message'] ?? 'Gagal memperbarui foto profil';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  // Logout
  Future<void> logout() async {
    try {
      await ApiClient.instance.post(ApiEndpoints.logout);
    } catch (_) {
      // Tetap lanjutkan logout lokal meskipun API gagal
    }

    _token = null;
    _user = null;
    await StorageService.clearAll();
    notifyListeners();
  }
}
