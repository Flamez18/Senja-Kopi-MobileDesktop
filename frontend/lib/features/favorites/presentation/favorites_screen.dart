import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  // Dummy data for now (would be from API/provider)
  final _favorites = [
    {
      'name': 'Kopi Susu Gula Aren',
      'category': 'Kopi',
      'price': 28000,
      'rating': 4.9,
      'branch': 'Sudirman',
      'imageUrl': 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=300&q=80',
    },
    {
      'name': 'Caramel Macchiato',
      'category': 'Kopi',
      'price': 35000,
      'rating': 4.7,
      'branch': 'Kemang',
      'imageUrl': 'https://images.unsplash.com/photo-1485808191679-5f86510bd9d4?auto=format&fit=crop&w=300&q=80',
    },
    {
      'name': 'Matcha Latte',
      'category': 'Non-Kopi',
      'price': 32000,
      'rating': 4.8,
      'branch': 'Sudirman',
      'imageUrl': 'https://images.unsplash.com/photo-1515823662972-da6a2e4d3002?auto=format&fit=crop&w=300&q=80',
    },
  ];

  final _favoriteBranches = [
    {
      'name': 'Kopi Senja – Sudirman',
      'address': 'Jl. Jend. Sudirman No. 21, Jakarta Pusat',
      'distance': '1.2 km',
      'openTime': '07:00 – 22:00',
      'imageUrl': 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=400&q=80',
    },
    {
      'name': 'Kopi Senja – Kemang',
      'address': 'Jl. Kemang Raya No. 5, Jakarta Selatan',
      'distance': '3.8 km',
      'openTime': '08:00 – 23:00',
      'imageUrl': 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=400&q=80',
    },
  ];

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: AppColors.bgBody,
        appBar: AppBar(
          title: const Text('Favorit'),
          bottom: const TabBar(
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.textMuted,
            indicatorColor: AppColors.primary,
            tabs: [
              Tab(text: 'Menu Favorit'),
              Tab(text: 'Cabang Favorit'),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            // Menu Favorites
            _favorites.isEmpty
                ? _emptyState('Belum ada menu favorit', Icons.coffee_outlined)
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _favorites.length,
                    itemBuilder: (ctx, idx) {
                      final fav = _favorites[idx];
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
                        ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: Row(
                            children: [
                              SizedBox(
                                width: 90,
                                height: 90,
                                child: CachedNetworkImage(
                                  imageUrl: fav['imageUrl'] as String,
                                  fit: BoxFit.cover,
                                  errorWidget: (_, __, ___) => Container(color: AppColors.creamDark),
                                ),
                              ),
                              Expanded(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: AppColors.creamDark,
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text(fav['category'] as String, style: const TextStyle(fontSize: 10, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                                          ),
                                          const Spacer(),
                                          const Icon(Icons.star_rounded, color: AppColors.accentGold, size: 14),
                                          const SizedBox(width: 2),
                                          Text('${fav['rating']}', style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                                        ],
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        fav['name'] as String,
                                        style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark, fontSize: 14, fontFamily: 'Plus Jakarta Sans'),
                                      ),
                                      const SizedBox(height: 2),
                                      Text('Cabang ${fav['branch']}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                                      const SizedBox(height: 6),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            CurrencyFormatter.toRupiah(fav['price'] as int),
                                            style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontSize: 13, fontFamily: 'Plus Jakarta Sans'),
                                          ),
                                          Row(
                                            children: [
                                              GestureDetector(
                                                onTap: () {
                                                  setState(() => _favorites.removeAt(idx));
                                                },
                                                child: const Icon(Icons.favorite_rounded, color: AppColors.danger, size: 20),
                                              ),
                                              const SizedBox(width: 8),
                                              Container(
                                                padding: const EdgeInsets.all(5),
                                                decoration: const BoxDecoration(color: AppColors.primary, shape: BoxShape.circle),
                                                child: const Icon(Icons.add, size: 14, color: Colors.white),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),

            // Branch Favorites
            _favoriteBranches.isEmpty
                ? _emptyState('Belum ada cabang favorit', Icons.store_outlined)
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _favoriteBranches.length,
                    itemBuilder: (ctx, idx) {
                      final branch = _favoriteBranches[idx];
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
                        ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: Column(
                            children: [
                              Stack(
                                children: [
                                  SizedBox(
                                    width: double.infinity,
                                    height: 110,
                                    child: CachedNetworkImage(
                                      imageUrl: branch['imageUrl'] as String,
                                      fit: BoxFit.cover,
                                      errorWidget: (_, __, ___) => Container(color: AppColors.creamDark),
                                    ),
                                  ),
                                  Container(
                                    height: 110,
                                    decoration: BoxDecoration(
                                      gradient: LinearGradient(
                                        colors: [AppColors.primary.withOpacity(0.6), Colors.transparent],
                                        begin: Alignment.bottomLeft,
                                        end: Alignment.topRight,
                                      ),
                                    ),
                                  ),
                                  Positioned(
                                    top: 8,
                                    right: 8,
                                    child: Container(
                                      padding: const EdgeInsets.all(6),
                                      decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                                      child: const Icon(Icons.favorite_rounded, color: AppColors.danger, size: 16),
                                    ),
                                  ),
                                ],
                              ),
                              Padding(
                                padding: const EdgeInsets.all(14),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(branch['name'] as String, style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark, fontFamily: 'Plus Jakarta Sans')),
                                          const SizedBox(height: 4),
                                          Text(branch['address'] as String, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans'), maxLines: 1, overflow: TextOverflow.ellipsis),
                                          const SizedBox(height: 6),
                                          Row(
                                            children: [
                                              const Icon(Icons.access_time_rounded, size: 13, color: AppColors.accentGold),
                                              const SizedBox(width: 4),
                                              Text(branch['openTime'] as String, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                                              const SizedBox(width: 12),
                                              const Icon(Icons.near_me_rounded, size: 13, color: AppColors.primary),
                                              const SizedBox(width: 4),
                                              Text(branch['distance'] as String, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans')),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                    ElevatedButton(
                                      onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (r) => false),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: AppColors.primary,
                                        foregroundColor: AppColors.cream,
                                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                        minimumSize: Size.zero,
                                        textStyle: const TextStyle(fontSize: 12),
                                      ),
                                      child: const Text('Pesan'),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ],
        ),
      ),
    );
  }

  Widget _emptyState(String message, IconData icon) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 64, color: AppColors.creamDark),
          const SizedBox(height: 12),
          Text(message, style: const TextStyle(color: AppColors.textMuted, fontFamily: 'Plus Jakarta Sans', fontSize: 15)),
        ],
      ),
    );
  }
}
