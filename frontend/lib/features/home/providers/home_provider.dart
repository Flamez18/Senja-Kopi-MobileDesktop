import 'package:flutter/material.dart';
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/models/banner.dart';
import '../../../core/models/category.dart';
import '../../../core/models/product.dart';

class HomeProvider extends ChangeNotifier {
  List<BannerModel> _banners = [];
  List<Category> _categories = [];
  List<Product> _products = [];
  bool _isBannersLoading = false;
  bool _isProductsLoading = false;
  String? _errorMessage;
  int? _selectedCategoryId;
  String _searchQuery = '';

  List<BannerModel> get banners => _banners;
  List<Category> get categories => _categories;
  List<Product> get products => _products;
  bool get isBannersLoading => _isBannersLoading;
  bool get isProductsLoading => _isProductsLoading;
  String? get errorMessage => _errorMessage;
  int? get selectedCategoryId => _selectedCategoryId;
  String get searchQuery => _searchQuery;

  // Fetch banners and categories for home screen
  Future<void> fetchHomeData(int branchId) async {
    _isBannersLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final bannersRes = await ApiClient.instance.get(ApiEndpoints.banners);
      if (bannersRes.data['success'] == true) {
        final List bannerList = bannersRes.data['data'];
        _banners = bannerList.map((b) => BannerModel.fromJson(b)).toList();
      }

      final categoriesRes = await ApiClient.instance.get(ApiEndpoints.categories);
      if (categoriesRes.data['success'] == true) {
        final List catList = categoriesRes.data['data'];
        _categories = catList.map((c) => Category.fromJson(c)).toList();
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (_) {
      _errorMessage = 'Gagal terhubung ke server';
    }

    _isBannersLoading = false;
    notifyListeners();

    // After header data, fetch products
    await fetchProducts(branchId);
  }

  // Fetch products with optional category/search filter
  Future<void> fetchProducts(int branchId, {int? categoryId, String? search}) async {
    _isProductsLoading = true;
    _errorMessage = null;
    notifyListeners();

    final Map<String, dynamic> params = {'branch_id': branchId};
    if (categoryId != null) params['category_id'] = categoryId;
    if (search != null && search.isNotEmpty) params['search'] = search;

    try {
      final response = await ApiClient.instance.get(
        ApiEndpoints.products,
        queryParameters: params,
      );
      if (response.data['success'] == true) {
        final List productList = response.data['data'];
        _products = productList.map((p) => Product.fromJson(p)).toList();
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (_) {
      _errorMessage = 'Gagal mengambil data menu';
    }

    _isProductsLoading = false;
    notifyListeners();
  }

  void setSelectedCategory(int? categoryId, int branchId) {
    _selectedCategoryId = categoryId;
    fetchProducts(branchId, categoryId: categoryId, search: _searchQuery.isNotEmpty ? _searchQuery : null);
  }

  void setSearchQuery(String query, int branchId) {
    _searchQuery = query;
    fetchProducts(branchId, categoryId: _selectedCategoryId, search: query.isNotEmpty ? query : null);
  }
}
