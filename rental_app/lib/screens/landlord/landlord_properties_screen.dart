import 'package:flutter/material.dart';
import 'package:rental_app/models/property_model.dart';
import 'package:rental_app/models/application_status.dart';
import 'package:rental_app/utils/constants.dart';

class LandlordPropertiesScreen extends StatefulWidget {
  const LandlordPropertiesScreen({super.key});

  @override
  State<LandlordPropertiesScreen> createState() => _LandlordPropertiesScreenState();
}

class _LandlordPropertiesScreenState extends State<LandlordPropertiesScreen> {
  // TODO: Replace with actual data
  final List<Property> _mockProperties = [];
  final int _propertyLimit = 3; // Based on landlord tier
  final int _currentPropertyCount = 0;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Properties'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterOptions,
          ),
        ],
      ),
      body: Column(
        children: [
          _buildPropertyLimitBanner(),
          Expanded(child: _buildPropertiesList()),
        ],
      ),
      floatingActionButton: _canAddProperty()
          ? FloatingActionButton.extended(
              onPressed: () {
                Navigator.pushNamed(context, '/landlord/add-property');
              },
              icon: const Icon(Icons.add),
              label: const Text('Add Property'),
              backgroundColor: kPrimaryRed,
            )
          : null,
    );
  }

  bool _canAddProperty() {
    if (_propertyLimit == -1) return true; // Unlimited
    return _currentPropertyCount < _propertyLimit;
  }

  Widget _buildPropertyLimitBanner() {
    final remaining = _propertyLimit == -1
        ? 'Unlimited'
        : '${_propertyLimit - _currentPropertyCount}';
    
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: kPrimaryGradient,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha((255 * 0.1).round()),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          const Icon(Icons.business, color: Colors.white, size: 32),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$_currentPropertyCount / ${_propertyLimit == -1 ? "∞" : _propertyLimit} Properties',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '$remaining slots remaining',
                  style: const TextStyle(
                    fontSize: 14,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPropertiesList() {
    if (_mockProperties.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.home_work_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              'No properties yet',
              style: TextStyle(
                fontSize: 16,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
            const SizedBox(height: 8),
            if (_canAddProperty())
              ElevatedButton.icon(
                onPressed: () {
                  Navigator.pushNamed(context, '/landlord/add-property');
                },
                icon: const Icon(Icons.add),
                label: const Text('Add Your First Property'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimaryRed,
                  foregroundColor: Colors.white,
                ),
              ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _mockProperties.length,
      itemBuilder: (context, index) {
        final property = _mockProperties[index];
        return _buildPropertyCard(property);
      },
    );
  }

  Widget _buildPropertyCard(Property property) {
    Color statusColor;
    String statusText;
    IconData statusIcon;

    switch (property.approvalStatus) {
      case ApplicationStatus.pending:
        statusColor = kPrimaryOrange;
        statusText = 'Pending Approval';
        statusIcon = Icons.pending;
        break;
      case ApplicationStatus.approved:
        statusColor = Colors.green;
        statusText = 'Approved';
        statusIcon = Icons.check_circle;
        break;
      case ApplicationStatus.rejected:
        statusColor = kPrimaryRed;
        statusText = 'Rejected';
        statusIcon = Icons.cancel;
        break;
      case ApplicationStatus.underReview:
        statusColor = Colors.blue;
        statusText = 'Under Review';
        statusIcon = Icons.rate_review;
        break;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Property Image
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                child: Image.network(
                  property.primaryImage,
                  height: 180,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) {
                    return Container(
                      height: 180,
                      color: kTextColorGrey.withAlpha((255 * 0.2).round()),
                      child: const Center(
                        child: Icon(Icons.image_not_supported, size: 48),
                      ),
                    );
                  },
                ),
              ),
              Positioned(
                top: 12,
                right: 12,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: statusColor,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(statusIcon, color: Colors.white, size: 16),
                      const SizedBox(width: 4),
                      Text(
                        statusText,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  property.title,
                  style: const TextStyle(
                    fontSize: 18,
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
                        property.location,
                        style: TextStyle(
                          fontSize: 14,
                          color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    _buildStat(Icons.visibility, '${property.viewCount}'),
                    const SizedBox(width: 16),
                    _buildStat(Icons.favorite, '${property.likeCount}'),
                    const SizedBox(width: 16),
                    _buildStat(Icons.star, property.rating.toStringAsFixed(1)),
                    const Spacer(),
                    Text(
                      'K${property.pricePerMonth.toStringAsFixed(0)}/mo',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: kPrimaryRed,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.pushNamed(
                            context,
                            '/landlord/edit-property',
                            arguments: property,
                          );
                        },
                        icon: const Icon(Icons.edit, size: 18),
                        label: const Text('Edit'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: kPrimaryRed,
                          side: const BorderSide(color: kPrimaryRed),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _showDeleteDialog(property),
                        icon: const Icon(Icons.delete, size: 18),
                        label: const Text('Delete'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: kTextColorGrey,
                          side: BorderSide(color: kTextColorGrey.withAlpha((255 * 0.5).round())),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStat(IconData icon, String value) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: kTextColorGrey),
        const SizedBox(width: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 12,
            color: kTextColorGrey.withAlpha((255 * 0.8).round()),
          ),
        ),
      ],
    );
  }

  void _showFilterOptions() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Filter Properties',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 16),
              ListTile(
                leading: const Icon(Icons.check_circle, color: Colors.green),
                title: const Text('Approved'),
                onTap: () {
                  Navigator.pop(context);
                  // TODO: Apply filter
                },
              ),
              ListTile(
                leading: const Icon(Icons.pending, color: kPrimaryOrange),
                title: const Text('Pending'),
                onTap: () {
                  Navigator.pop(context);
                  // TODO: Apply filter
                },
              ),
              ListTile(
                leading: const Icon(Icons.cancel, color: kPrimaryRed),
                title: const Text('Rejected'),
                onTap: () {
                  Navigator.pop(context);
                  // TODO: Apply filter
                },
              ),
            ],
          ),
        );
      },
    );
  }

  void _showDeleteDialog(Property property) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Property'),
        content: Text('Are you sure you want to delete "${property.title}"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              // TODO: Implement delete
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Property deleted'),
                  backgroundColor: kPrimaryRed,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: kPrimaryRed,
              foregroundColor: Colors.white,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }
}
