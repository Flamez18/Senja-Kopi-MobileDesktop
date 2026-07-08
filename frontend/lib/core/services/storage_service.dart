import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static SharedPreferences? _prefs;

  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  // Token keys
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'user_data';
  static const String _keyBranch = 'selected_branch';
  static const String _keyFavoriteBranches = 'favorite_branches';
  static const String _keyDefaultPayment = 'default_payment';

  // Auth Token
  static String? getToken() {
    return _prefs?.getString(_keyToken);
  }

  static Future<bool> saveToken(String token) async {
    return await _prefs?.setString(_keyToken, token) ?? false;
  }

  static Future<bool> clearToken() async {
    return await _prefs?.remove(_keyToken) ?? false;
  }

  // User details JSON
  static String? getUserJson() {
    return _prefs?.getString(_keyUser);
  }

  static Future<bool> saveUserJson(String userJson) async {
    return await _prefs?.setString(_keyUser, userJson) ?? false;
  }

  static Future<bool> clearUser() async {
    return await _prefs?.remove(_keyUser) ?? false;
  }

  // Selected Branch JSON
  static String? getSelectedBranchJson() {
    return _prefs?.getString(_keyBranch);
  }

  static Future<bool> saveSelectedBranchJson(String branchJson) async {
    return await _prefs?.setString(_keyBranch, branchJson) ?? false;
  }

  static Future<bool> clearSelectedBranch() async {
    return await _prefs?.remove(_keyBranch) ?? false;
  }

  // Favorite Branches (List of IDs)
  static List<String> getFavoriteBranches() {
    return _prefs?.getStringList(_keyFavoriteBranches) ?? [];
  }

  static Future<bool> saveFavoriteBranches(List<String> branchIds) async {
    return await _prefs?.setStringList(_keyFavoriteBranches, branchIds) ?? false;
  }

  // Default Payment Method
  static String? getDefaultPayment() {
    return _prefs?.getString(_keyDefaultPayment);
  }

  static Future<bool> saveDefaultPayment(String method) async {
    return await _prefs?.setString(_keyDefaultPayment, method) ?? false;
  }

  // Clear all storage (for logout)
  static Future<void> clearAll() async {
    await _prefs?.clear();
  }
}
