import 'dart:convert';
import 'package:flutter/material.dart';
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/models/branch.dart';
import '../../../core/services/storage_service.dart';

class BranchProvider extends ChangeNotifier {
  List<Branch> _branches = [];
  Branch? _selectedBranch;
  List<String> _favoriteBranchIds = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<Branch> get branches => _branches;
  Branch? get selectedBranch => _selectedBranch;
  List<String> get favoriteBranchIds => _favoriteBranchIds;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  BranchProvider() {
    loadSavedBranch();
    loadFavoriteBranches();
  }

  // Load saved branch on startup
  void loadSavedBranch() {
    final branchJson = StorageService.getSelectedBranchJson();
    if (branchJson != null) {
      try {
        _selectedBranch = Branch.fromJson(json.decode(branchJson));
      } catch (_) {
        _selectedBranch = null;
      }
    }
    notifyListeners();
  }

  // Load favorite branch IDs on startup
  void loadFavoriteBranches() {
    _favoriteBranchIds = StorageService.getFavoriteBranches();
    notifyListeners();
  }

  // Check if a branch is favorited
  bool isFavorite(int branchId) {
    return _favoriteBranchIds.contains(branchId.toString());
  }

  // Toggle favorite branch status
  Future<void> toggleFavoriteBranch(int branchId) async {
    final idStr = branchId.toString();
    if (_favoriteBranchIds.contains(idStr)) {
      _favoriteBranchIds.remove(idStr);
    } else {
      _favoriteBranchIds.add(idStr);
    }
    await StorageService.saveFavoriteBranches(_favoriteBranchIds);
    notifyListeners();
  }

  // Set branch as utama (primary selected)
  Future<void> selectBranch(Branch branch) async {
    _selectedBranch = branch;
    await StorageService.saveSelectedBranchJson(json.encode(branch.toJson()));
    notifyListeners();
  }

  // Fetch branches from backend
  Future<void> fetchBranches() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiClient.instance.get(ApiEndpoints.branches);
      if (response.data['success'] == true) {
        final List dataList = response.data['data'];
        _branches = dataList.map((item) => Branch.fromJson(item)).toList();
      } else {
        _errorMessage = response.data['message'] ?? 'Gagal mengambil data cabang';
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (_) {
      _errorMessage = 'Gagal terhubung ke server';
    }

    _isLoading = false;
    notifyListeners();
  }
}
