import 'package:flutter/material.dart';
import 'package:rental_app/models/property_model.dart';
import 'package:rental_app/models/application_status.dart';
import 'package:rental_app/utils/constants.dart';

class PropertyApprovalScreen extends StatefulWidget {
  const PropertyApprovalScreen({super.key});

  @override
  State<PropertyApprovalScreen> createState() => _PropertyApprovalScreenState();
}

class _PropertyApprovalScreenState extends State<PropertyApprovalScreen> {
  ApplicationStatus _filterStatus = ApplicationStatus.pending;
  
  // TODO: Replace with actual service call
  final List<Property> _mockProperties = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Property Approvals'),
        actions: [
          PopupMenuButton<ApplicationStatus>(
            icon: const Icon(Icons.filter_list),
            initialValue: _filterStatus,
            onSelected: (status) {
              setState(() {
                _filterStatus = status;
              });
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: ApplicationStatus.pending,
                child: Text(ApplicationStatus.pending.displayName),
              ),
              PopupMenuItem(
                value: ApplicationStatus.approved,
                child: Text(ApplicationStatus.approved.displayName),
              ),
              PopupMenuItem(
                value: ApplicationStatus.rejected,
                child: Text(ApplicationStatus.rejected.displayName),
              ),
            ],
          ),
        ],
      ),
      body: _buildPropertiesList(),
    );
  }

  Widget _buildPropertiesList() {
    final filteredProperties = _mockProperties
        .where((property) => property.approvalStatus == _filterStatus)
        .toList();

    if (filteredProperties.isEmpty) {
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
              'No ${_filterStatus.displayName.toLowerCase()} properties',
              style: TextStyle(
                fontSize: 16,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredProperties.length,
      itemBuilder: (context, index) {
        final property = filteredProperties[index];
        return _buildPropertyCard(property);
      },
    );
  }

  Widget _buildPropertyCard(Property property) {
    Color statusColor;
    IconData statusIcon;

    switch (property.approvalStatus) {
      case ApplicationStatus.pending:
        statusColor = kPrimaryOrange;
        statusIcon = Icons.pending_outlined;
        break;
      case ApplicationStatus.underReview:
        statusColor = Colors.blue;
        statusIcon = Icons.rate_review_outlined;
        break;
      case ApplicationStatus.approved:
        statusColor = Colors.green;
        statusIcon = Icons.check_circle_outline;
        break;
      case ApplicationStatus.rejected:
        statusColor = kPrimaryRed;
        statusIcon = Icons.cancel_outlined;
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
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
            child: Stack(
              children: [
                Image.network(
                  property.primaryImage,
                  height: 200,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) {
                    return Container(
                      height: 200,
                      color: kTextColorGrey.withAlpha((255 * 0.2).round()),
                      child: const Center(
                        child: Icon(Icons.image_not_supported, size: 48),
                      ),
                    );
                  },
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
                          property.approvalStatus.displayName,
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
                    _buildPropertyFeature(Icons.bed, '${property.beds} Beds'),
                    const SizedBox(width: 16),
                    _buildPropertyFeature(Icons.bathtub, '${property.baths} Baths'),
                    const SizedBox(width: 16),
                    _buildPropertyFeature(Icons.square_foot, '${property.sqft} sqft'),
                  ],
                ),
                const SizedBox(height: 12),
                const Divider(height: 1),
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Icon(Icons.person_outline, size: 16, color: kTextColorGrey),
                    const SizedBox(width: 4),
                    Text(
                      'Landlord: ${property.landlordName}',
                      style: TextStyle(
                        fontSize: 14,
                        color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                      ),
                    ),
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
                if (property.approvalStatus == ApplicationStatus.pending) ...[
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _showRejectDialog(property),
                          icon: const Icon(Icons.close, size: 18),
                          label: const Text('Reject'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: kPrimaryRed,
                            side: const BorderSide(color: kPrimaryRed),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () => _showApproveDialog(property),
                          icon: const Icon(Icons.check, size: 18),
                          label: const Text('Approve'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.green,
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: 8),
                TextButton.icon(
                  onPressed: () {
                    _showPropertyDetails(property);
                  },
                  icon: const Icon(Icons.visibility, size: 18),
                  label: const Text('View Full Details'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPropertyFeature(IconData icon, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: kTextColorGrey),
        const SizedBox(width: 4),
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: kTextColorGrey.withAlpha((255 * 0.8).round()),
          ),
        ),
      ],
    );
  }

  void _showPropertyDetails(Property property) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (context, scrollController) {
          return SingleChildScrollView(
            controller: scrollController,
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: kTextColorGrey.withAlpha((255 * 0.3).round()),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  property.title,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: kTextColorBlack,
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Description',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: kTextColorBlack,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  property.description,
                  style: TextStyle(
                    fontSize: 14,
                    color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                    height: 1.5,
                  ),
                ),
                const SizedBox(height: 24),
                const Text(
                  'Amenities',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: kTextColorBlack,
                  ),
                ),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: property.amenities.map((amenity) {
                    return Chip(
                      label: Text(amenity),
                      backgroundColor: kPrimaryRed.withAlpha((255 * 0.1).round()),
                      labelStyle: const TextStyle(
                        color: kPrimaryRed,
                        fontSize: 12,
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 24),
                const Text(
                  'Images',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: kTextColorBlack,
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  height: 120,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: property.imageUrls.length,
                    itemBuilder: (context, index) {
                      return Container(
                        margin: const EdgeInsets.only(right: 12),
                        width: 160,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          image: DecorationImage(
                            image: NetworkImage(property.imageUrls[index]),
                            fit: BoxFit.cover,
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  void _showApproveDialog(Property property) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Approve Property'),
        content: Text('Are you sure you want to approve "${property.title}"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              // TODO: Implement approve logic
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Property approved successfully'),
                  backgroundColor: Colors.green,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
            ),
            child: const Text('Approve'),
          ),
        ],
      ),
    );
  }

  void _showRejectDialog(Property property) {
    final TextEditingController reasonController = TextEditingController();
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Reject Property'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Are you sure you want to reject "${property.title}"?'),
            const SizedBox(height: 16),
            TextField(
              controller: reasonController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Rejection Reason',
                hintText: 'e.g., Missing images, incomplete information...',
                border: OutlineInputBorder(),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              // TODO: Implement reject logic
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Property rejected'),
                  backgroundColor: kPrimaryRed,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: kPrimaryRed,
              foregroundColor: Colors.white,
            ),
            child: const Text('Reject'),
          ),
        ],
      ),
    );
  }
}
