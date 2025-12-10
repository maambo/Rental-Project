import 'package:flutter/material.dart';
import 'package:rental_app/models/property_model.dart';
import 'package:rental_app/utils/constants.dart';

class PropertyDetailScreen extends StatefulWidget {
  final Property property;

  const PropertyDetailScreen({super.key, required this.property});

  @override
  State<PropertyDetailScreen> createState() => _PropertyDetailScreenState();
}

class _PropertyDetailScreenState extends State<PropertyDetailScreen> {
  int _currentImageIndex = 0;
  bool _isLiked = false; // TODO: Get from user's liked properties
  final bool _isAuthenticated = false; // TODO: Get from auth service

  @override
  void initState() {
    super.initState();
    // TODO: Increment view count
    _incrementViewCount();
  }

  void _incrementViewCount() {
    // TODO: Call backend to increment view count
    print('View count incremented for property: ${widget.property.id}');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          _buildAppBar(),
          SliverToBoxAdapter(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildImageGallery(),
                _buildPropertyHeader(),
                const Divider(height: 1),
                _buildPropertyDetails(),
                const Divider(height: 1),
                _buildAmenities(),
                const Divider(height: 1),
                _buildDescription(),
                const Divider(height: 1),
                _buildReviewsSection(),
                const Divider(height: 1),
                _buildLocationSection(),
                const SizedBox(height: 100), // Space for bottom buttons
              ],
            ),
          ),
        ],
      ),
      bottomNavigationBar: _buildBottomActions(),
    );
  }

  Widget _buildAppBar() {
    return SliverAppBar(
      expandedHeight: 300,
      pinned: true,
      flexibleSpace: FlexibleSpaceBar(
        background: _buildImageGallery(),
      ),
      actions: [
        IconButton(
          icon: Icon(
            _isLiked ? Icons.favorite : Icons.favorite_border,
            color: _isLiked ? kPrimaryRed : Colors.white,
          ),
          onPressed: _handleLikeToggle,
        ),
        IconButton(
          icon: const Icon(Icons.share, color: Colors.white),
          onPressed: () {
            // TODO: Implement share functionality
          },
        ),
      ],
    );
  }

  Widget _buildImageGallery() {
    final images = widget.property.imageUrls;
    
    return Stack(
      children: [
        PageView.builder(
          itemCount: images.length,
          onPageChanged: (index) {
            setState(() {
              _currentImageIndex = index;
            });
          },
          itemBuilder: (context, index) {
            return Image.network(
              images[index],
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) {
                return Container(
                  color: kTextColorGrey.withAlpha((255 * 0.2).round()),
                  child: const Center(
                    child: Icon(Icons.image_not_supported, size: 64),
                  ),
                );
              },
            );
          },
        ),
        Positioned(
          bottom: 16,
          right: 16,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.black.withAlpha((255 * 0.6).round()),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              '${_currentImageIndex + 1}/${images.length}',
              style: const TextStyle(color: Colors.white, fontSize: 12),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPropertyHeader() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            widget.property.title,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: kTextColorBlack,
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              const Icon(Icons.location_on, size: 16, color: kPrimaryRed),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  widget.property.location,
                  style: TextStyle(
                    fontSize: 14,
                    color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'K${widget.property.price.toStringAsFixed(0)}/month',
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: kPrimaryRed,
                ),
              ),
              Row(
                children: [
                  const Icon(Icons.star, color: Colors.amber, size: 20),
                  const SizedBox(width: 4),
                  Text(
                    '4.5', // TODO: Calculate from reviews
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  Text(
                    ' (12)', // TODO: Get review count
                    style: TextStyle(
                      fontSize: 14,
                      color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPropertyDetails() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Property Details',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: kTextColorBlack,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _buildDetailItem(
                  Icons.bed,
                  '${widget.property.bedrooms} Beds',
                ),
              ),
              Expanded(
                child: _buildDetailItem(
                  Icons.bathtub,
                  '${widget.property.bathrooms} Baths',
                ),
              ),
              Expanded(
                child: _buildDetailItem(
                  Icons.square_foot,
                  '${widget.property.sqft} sqft',
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDetailItem(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: kPrimaryRed.withAlpha((255 * 0.05).round()),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          Icon(icon, color: kPrimaryRed, size: 28),
          const SizedBox(height: 8),
          Text(
            label,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAmenities() {
    // TODO: Get from property amenities list
    final amenities = [
      'WiFi',
      'Parking',
      'Air Conditioning',
      'Security',
      'Garden',
      'Balcony',
    ];

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Amenities',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: kTextColorBlack,
            ),
          ),
          const SizedBox(height: 16),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: amenities.map((amenity) {
              return Chip(
                label: Text(amenity),
                backgroundColor: Colors.blue.withAlpha((255 * 0.1).round()),
                labelStyle: const TextStyle(
                  color: Colors.blue,
                  fontSize: 12,
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildDescription() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Description',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: kTextColorBlack,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            widget.property.description,
            style: TextStyle(
              fontSize: 14,
              color: kTextColorGrey.withAlpha((255 * 0.9).round()),
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildReviewsSection() {
    // TODO: Get actual reviews from backend
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Reviews',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: kTextColorBlack,
                ),
              ),
              TextButton(
                onPressed: _handleWriteReview,
                child: const Text('Write Review'),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildReviewCard('John Doe', 5, 'Great property! Very clean and well-maintained.', '2 days ago'),
          _buildReviewCard('Jane Smith', 4, 'Good location, friendly landlord.', '1 week ago'),
        ],
      ),
    );
  }

  Widget _buildReviewCard(String userName, int rating, String comment, String time) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey.withAlpha((255 * 0.05).round()),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                userName,
                style: const TextStyle(
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
              Row(
                children: List.generate(5, (index) {
                  return Icon(
                    index < rating ? Icons.star : Icons.star_border,
                    color: Colors.amber,
                    size: 16,
                  );
                }),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            comment,
            style: TextStyle(
              fontSize: 13,
              color: kTextColorGrey.withAlpha((255 * 0.9).round()),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            time,
            style: TextStyle(
              fontSize: 11,
              color: kTextColorGrey.withAlpha((255 * 0.6).round()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLocationSection() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Location',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: kTextColorBlack,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            height: 200,
            decoration: BoxDecoration(
              color: kTextColorGrey.withAlpha((255 * 0.2).round()),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.map, size: 48),
                  SizedBox(height: 8),
                  Text('Map View'),
                  Text('(To be implemented)', style: TextStyle(fontSize: 12)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomActions() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha((255 * 0.1).round()),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: OutlinedButton.icon(
              onPressed: _handleScheduleTour,
              icon: const Icon(Icons.tour),
              label: const Text('Schedule Tour'),
              style: OutlinedButton.styleFrom(
                foregroundColor: kPrimaryRed,
                side: const BorderSide(color: kPrimaryRed),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: ElevatedButton.icon(
              onPressed: _handleApply,
              icon: const Icon(Icons.assignment),
              label: const Text('Apply Now'),
              style: ElevatedButton.styleFrom(
                backgroundColor: kPrimaryRed,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _handleLikeToggle() {
    if (!_isAuthenticated) {
      _showAuthRequiredDialog('like this property');
      return;
    }
    
    setState(() {
      _isLiked = !_isLiked;
    });
    // TODO: Update like in backend
  }

  void _handleWriteReview() {
    if (!_isAuthenticated) {
      _showAuthRequiredDialog('write a review');
      return;
    }
    
    Navigator.pushNamed(
      context,
      '/property/${widget.property.id}/review',
      arguments: widget.property,
    );
  }

  void _handleScheduleTour() {
    if (!_isAuthenticated) {
      _showAuthRequiredDialog('schedule a tour');
      return;
    }
    
    Navigator.pushNamed(
      context,
      '/property/${widget.property.id}/tour',
      arguments: widget.property,
    );
  }

  void _handleApply() {
    if (!_isAuthenticated) {
      _showAuthRequiredDialog('apply for this property');
      return;
    }
    
    Navigator.pushNamed(
      context,
      '/property/${widget.property.id}/apply',
      arguments: widget.property,
    );
  }

  void _showAuthRequiredDialog(String action) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sign In Required'),
        content: Text('Please sign in with your Google account to $action.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.pushNamed(context, '/login');
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: kPrimaryRed,
              foregroundColor: Colors.white,
            ),
            child: const Text('Sign In'),
          ),
        ],
      ),
    );
  }
}
