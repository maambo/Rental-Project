import 'package:flutter/material.dart';
import 'package:rental_app/models/property_model.dart';
import 'package:rental_app/services/data_service.dart';
import 'package:rental_app/utils/constants.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import 'package:rental_app/widgets/property_stat_widget.dart';

class LandingScreen extends StatefulWidget {
  const LandingScreen({super.key});

  @override
  State<LandingScreen> createState() => _LandingScreenState();
}

class _LandingScreenState extends State<LandingScreen> {
  late Future<List<Property>> _propertiesFuture;
  Property? _selectedProperty;
  final List<String> _favoritePropertyIds = []; // For mock favorites
  bool _showMap = false;
  String? _highlightedPropertyId;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _propertiesFuture = DataService().loadProperties();
  }

  void _toggleFavorite(String propertyId) {
    setState(() {
      if (_favoritePropertyIds.contains(propertyId)) {
        _favoritePropertyIds.remove(propertyId);
      } else {
        _favoritePropertyIds.add(propertyId);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _selectedProperty == null
          ? _buildPropertyListView(context)
          : _buildPropertyDetailView(context, _selectedProperty!),
    );
  }

  Widget _buildPropertyListView(BuildContext context) {
    return FutureBuilder<List<Property>>(
      future: _propertiesFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        } else if (snapshot.hasError) {
          return Center(child: Text('Error: ${snapshot.error}'));
        } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
          return const Center(child: Text('No properties available.'));
        } else {
          final properties = snapshot.data!;
          return CustomScrollView(
            controller: _scrollController,
            slivers: [
              SliverAppBar(
                expandedHeight: 150.0,
                floating: true,
                pinned: true,
                flexibleSpace: FlexibleSpaceBar(
                  background: Container(
                    decoration: const BoxDecoration(
                      gradient: kPrimaryGradient,
                    ),
                    child: Padding(
                      padding: const EdgeInsets.only(left: 16.0, bottom: 16.0, top: 48.0),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.end,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Find Your Perfect Rental',
                            style: TextStyle(
                              color: kTextColorWhite,
                              fontSize: 28,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'Discover amazing properties in Lusaka',
                            style: TextStyle(
                              color: kTextColorWhite.withAlpha((255 * 0.9).round()),
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                actions: [
                  TextButton(
                    onPressed: () {
                      Navigator.pushNamed(context, '/landlord/register');
                    },
                    child: const Text(
                      'Become a Landlord',
                      style: TextStyle(color: kTextColorWhite, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(width: 8),
                  TextButton(
                    onPressed: () {
                      Navigator.pushReplacementNamed(context, '/'); // Navigate to login
                    },
                    child: const Text(
                      'Login',
                      style: TextStyle(color: kTextColorWhite),
                    ),
                  ),
                ],
              ),
              if (_showMap)
                SliverToBoxAdapter(
                  child: SizedBox(
                    height: 300,
                    child: FlutterMap(
                      options: MapOptions(
                        initialCenter: const LatLng(-15.4000, 28.3000), // Center of Lusaka
                        initialZoom: 12.0,
                      ),
                      children: [
                        TileLayer(
                          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                          userAgentPackageName: 'com.rentalapp.app',
                        ),
                        MarkerLayer(
                          markers: properties.map((property) {
                            final isHighlighted = property.id == _highlightedPropertyId;
                            return Marker(
                              point: LatLng(property.latitude, property.longitude),
                              width: 40,
                              height: 40,
                              child: GestureDetector(
                                onTap: () {
                                  setState(() {
                                    _selectedProperty = property;
                                  });
                                },
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: isHighlighted ? Colors.green : kPrimaryRed,
                                    shape: BoxShape.circle,
                                    border: Border.all(color: Colors.white, width: 2),
                                    boxShadow: [
                                      BoxShadow(
                                        color: Colors.black.withAlpha(50),
                                        blurRadius: 4,
                                        offset: const Offset(0, 2),
                                      ),
                                    ],
                                  ),
                                  child: const Icon(
                                    Icons.home,
                                    color: Colors.white,
                                    size: 20,
                                  ),
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                  ),
                ),
              // Landlord CTA Section
              SliverToBoxAdapter(
                child: Container(
                  margin: const EdgeInsets.all(16.0),
                  padding: const EdgeInsets.all(24.0),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFFE21608), Color(0xFFFF6B35)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: kPrimaryRed.withAlpha((255 * 0.3).round()),
                        blurRadius: 15,
                        offset: const Offset(0, 5),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: kTextColorWhite.withAlpha((255 * 0.2).round()),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(
                              Icons.business,
                              color: kTextColorWhite,
                              size: 32,
                            ),
                          ),
                          const SizedBox(width: 16),
                          const Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Own Properties?',
                                  style: TextStyle(
                                    color: kTextColorWhite,
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Join our platform and start earning',
                                  style: TextStyle(
                                    color: kTextColorWhite,
                                    fontSize: 14,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      Row(
                        children: [
                          Expanded(
                            child: _buildBenefitItem(
                              Icons.visibility,
                              'Maximum\nVisibility',
                            ),
                          ),
                          Expanded(
                            child: _buildBenefitItem(
                              Icons.people,
                              'Quality\nTenants',
                            ),
                          ),
                          Expanded(
                            child: _buildBenefitItem(
                              Icons.analytics,
                              'Property\nAnalytics',
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () {
                            Navigator.pushNamed(context, '/landlord/register');
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: kTextColorWhite,
                            foregroundColor: kPrimaryRed,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 0,
                          ),
                          child: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                'Become a Landlord',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              SizedBox(width: 8),
                              Icon(Icons.arrow_forward, size: 20),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              SliverPersistentHeader(
                delegate: _FilterBarDelegate(),
                pinned: true,
              ),
              SliverPadding(
                padding: const EdgeInsets.all(16.0),
                sliver: SliverGrid(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 1, // 1 column for mobile
                    mainAxisSpacing: 16.0,
                    crossAxisSpacing: 16.0,
                    childAspectRatio: 0.8, // Adjust as needed
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final property = properties[index];
                      final isFavorite = _favoritePropertyIds.contains(property.id);
                      return GestureDetector(
                        onTap: () {
                          setState(() {
                            _selectedProperty = property;
                          });
                        },
                        child: Card(
                          elevation: 4,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          clipBehavior: Clip.antiAlias,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Stack(
                                children: [
                                  Image.network(
                                    property.primaryImage,
                                    height: 200,
                                    width: double.infinity,
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) => Container(
                                      height: 200,
                                      color: Colors.grey,
                                      child: const Center(child: Icon(Icons.broken_image, color: Colors.white)),
                                    ),
                                  ),
                                  Positioned(
                                    top: 12,
                                    right: 12,
                                    child: GestureDetector(
                                      onTap: () => _toggleFavorite(property.id),
                                      child: CircleAvatar(
                                        backgroundColor: kTextColorWhite.withAlpha((255 * 0.9).round()),
                                        radius: 20,
                                        child: Icon(
                                          isFavorite ? Icons.favorite : Icons.favorite_border,
                                          color: isFavorite ? kPrimaryRed : kTextColorGrey,
                                          size: 24,
                                        ),
                                      ),
                                    ),
                                  ),
                                  Positioned(
                                    top: 12,
                                    left: 12,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: kPrimaryRed,
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: Text(
                                        property.type,
                                        style: const TextStyle(color: kTextColorWhite, fontSize: 12),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              Padding(
                                padding: const EdgeInsets.all(16.0),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          'K${property.pricePerMonth.toStringAsFixed(0)}/mo',
                                          style: const TextStyle(
                                            fontSize: 20,
                                            fontWeight: FontWeight.bold,
                                            color: kPrimaryRed,
                                          ),
                                        ),
                                        Row(
                                          children: [
                                            const Icon(Icons.star, color: Colors.amber, size: 16),
                                            const SizedBox(width: 4),
                                            Text(
                                              property.rating.toString(),
                                              style: const TextStyle(color: kTextColorBlack, fontSize: 14),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      property.title,
                                      style: const TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.bold,
                                        color: kTextColorBlack,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    const SizedBox(height: 4),
                                      Row(
                                        children: [
                                          const Icon(Icons.location_on, color: kPrimaryRed, size: 16),
                                          const SizedBox(width: 4),
                                          Expanded(
                                            child: Text(
                                              property.location,
                                              style: const TextStyle(color: kTextColorGrey, fontSize: 14),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.map, color: kPrimaryRed),
                                            onPressed: () {
                                              setState(() {
                                                _showMap = true;
                                                _highlightedPropertyId = property.id;
                                              });
                                              _scrollController.animateTo(
                                                0,
                                                duration: const Duration(milliseconds: 500),
                                                curve: Curves.easeInOut,
                                              );
                                            },
                                          ),
                                        ],
                                      ),
                                    const SizedBox(height: 8),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        PropertyStatWidget(icon: Icons.king_bed, text: '${property.beds} beds'),
                                        PropertyStatWidget(icon: Icons.bathtub, text: '${property.baths} baths'),
                                        PropertyStatWidget(icon: Icons.square_foot, text: '${property.sqft} sqft'),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                    childCount: properties.length,
                  ),
                ),
              ),
            ],
          );
        }
      },
    );
  }

  

  Widget _buildPropertyDetailView(BuildContext context, Property property) {
    final isFavorite = _favoritePropertyIds.contains(property.id);
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 250.0,
            pinned: true,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back, color: kTextColorWhite),
              onPressed: () {
                setState(() {
                  _selectedProperty = null;
                });
              },
            ),
            actions: [
              IconButton(
                icon: Icon(
                  isFavorite ? Icons.favorite : Icons.favorite_border,
                  color: kTextColorWhite,
                ),
                onPressed: () => _toggleFavorite(property.id),
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Image.network(
                property.primaryImage,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => Container(
                  color: Colors.grey,
                  child: const Center(child: Icon(Icons.broken_image, color: Colors.white)),
                ),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'K${property.pricePerMonth.toStringAsFixed(0)}/month',
                        style: const TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                          color: kPrimaryRed,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: kTextColorGrey.withAlpha((255 * 0.1).round()),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          property.type,
                          style: const TextStyle(color: kTextColorBlack, fontSize: 14),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    property.title,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: kTextColorBlack,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.location_on, color: kPrimaryRed, size: 20),
                      const SizedBox(width: 8),
                      Text(
                        property.location,
                        style: const TextStyle(color: kTextColorGrey, fontSize: 16),
                      ),
                      const Spacer(),
                      const Icon(Icons.star, color: Colors.amber, size: 20),
                      const SizedBox(width: 4),
                      Text(
                        '${property.rating} (${property.reviews} reviews)',
                        style: const TextStyle(color: kTextColorBlack, fontSize: 16),
                      ),
                    ],
                  ),
                  const Divider(height: 32),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      PropertyStatWidget(
                          icon: Icons.king_bed,
                          text: '${property.beds} Bedrooms',
                          isDetailed: true),
                      PropertyStatWidget(
                          icon: Icons.bathtub,
                          text: '${property.baths} Bathrooms',
                          isDetailed: true),
                      PropertyStatWidget(
                          icon: Icons.square_foot,
                          text: '${property.sqft} Sq Ft',
                          isDetailed: true),
                    ],
                  ),
                  const Divider(height: 32),
                  const Text(
                    'About This Property',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: kTextColorBlack,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    property.description,
                    style: const TextStyle(color: kTextColorBlack, fontSize: 16),
                  ),
                  const Divider(height: 32),
                  const Text(
                    'Amenities',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: kTextColorBlack,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8.0,
                    runSpacing: 8.0,
                    children: property.amenities
                        .map((amenity) => Chip(
                              label: Text(amenity, style: const TextStyle(color: kTextColorBlack)),
                              backgroundColor: kTextColorGrey.withAlpha((255 * 0.1).round()),
                            ))
                        .toList(),
                  ),
                  const Divider(height: 32),
                  const Text(
                    'Contact Landlord',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: kTextColorBlack,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFFFEBEE), Color(0xFFFFF3E0)], // Light red to light orange
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          property.landlordName,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: kTextColorBlack,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            const Icon(Icons.phone, color: kPrimaryRed, size: 20),
                            const SizedBox(width: 8),
                            Text(
                              property.landlordPhone,
                              style: const TextStyle(color: kTextColorBlack, fontSize: 16),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.mail, color: kPrimaryRed, size: 20),
                            const SizedBox(width: 8),
                            Text(
                              property.landlordEmail,
                              style: const TextStyle(color: kTextColorBlack, fontSize: 16),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          icon: const Icon(Icons.calendar_today, color: kPrimaryRed),
                          label: const Text('Schedule Tour', style: TextStyle(color: kPrimaryRed)),
                          onPressed: () {
                            // TODO: Implement schedule tour
                          },
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            side: const BorderSide(color: kPrimaryRed),
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: ElevatedButton.icon(
                          icon: const Icon(Icons.phone, color: kTextColorWhite),
                          label: const Text('Contact Now', style: TextStyle(color: kTextColorWhite)),
                          onPressed: () {
                            // TODO: Implement contact now
                          },
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            backgroundColor: kPrimaryRed, // Use primary color for solid button
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBenefitItem(IconData icon, String text) {
    return Column(
      children: [
        Icon(
          icon,
          color: kTextColorWhite,
          size: 28,
        ),
        const SizedBox(height: 8),
        Text(
          text,
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: kTextColorWhite,
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}

class _FilterBarDelegate extends SliverPersistentHeaderDelegate {
  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) {
    return Container(
      color: Theme.of(context).scaffoldBackgroundColor, // Use theme background color
      padding: const EdgeInsets.symmetric(vertical: 8.0, horizontal: 16.0),
      child: ListView(
        scrollDirection: Axis.horizontal,
        children: [
          _buildFilterButton(context, 'All Properties', true),
          _buildFilterButton(context, 'Apartments', false),
          _buildFilterButton(context, 'Houses', false),
          _buildFilterButton(context, 'Studios', false),
        ],
      ),
    );
  }

  Widget _buildFilterButton(BuildContext context, String text, bool isSelected) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4.0),
      child: ElevatedButton(
        onPressed: () {
          // TODO: Implement filter logic
        },
        style: ElevatedButton.styleFrom(
          backgroundColor: isSelected ? kPrimaryRed : kTextColorGrey.withAlpha((255 * 0.1).round()),
          foregroundColor: isSelected ? kTextColorWhite : kTextColorBlack,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          elevation: 0,
        ),
        child: Text(text),
      ),
    );
  }

  @override
  double get maxExtent => 60.0; // Height of the filter bar

  @override
  double get minExtent => 60.0; // Height of the filter bar

  @override
  bool shouldRebuild(covariant SliverPersistentHeaderDelegate oldDelegate) => false;
}