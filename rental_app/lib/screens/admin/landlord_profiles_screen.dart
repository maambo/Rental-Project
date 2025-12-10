import 'package:flutter/material.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/models/landlord_tier.dart';
import 'package:rental_app/models/application_status.dart';
import 'package:rental_app/utils/constants.dart';

class LandlordProfilesScreen extends StatefulWidget {
  const LandlordProfilesScreen({super.key});

  @override
  State<LandlordProfilesScreen> createState() => _LandlordProfilesScreenState();
}

class _LandlordProfilesScreenState extends State<LandlordProfilesScreen> {
  ApplicationStatus? _filterStatus;
  LandlordTier? _filterTier;
  
  // TODO: Replace with actual data from backend
  final List<User> _mockLandlords = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Landlord Profiles'),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.filter_list),
            onSelected: (value) {
              // Show filter dialog
              _showFilterDialog();
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'filter',
                child: Row(
                  children: [
                    Icon(Icons.filter_alt, size: 20),
                    SizedBox(width: 8),
                    Text('Filter'),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: _buildLandlordsList(),
    );
  }

  Widget _buildLandlordsList() {
    // Apply filters
    var filteredLandlords = _mockLandlords.where((landlord) {
      if (_filterStatus != null && landlord.applicationStatus != _filterStatus) {
        return false;
      }
      if (_filterTier != null && landlord.landlordTier != _filterTier) {
        return false;
      }
      return true;
    }).toList();

    if (filteredLandlords.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.business_center_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              _filterStatus != null || _filterTier != null
                  ? 'No landlords match the filters'
                  : 'No landlords registered yet',
              style: TextStyle(
                fontSize: 16,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
            if (_filterStatus != null || _filterTier != null) ...[
              const SizedBox(height: 12),
              TextButton.icon(
                onPressed: () {
                  setState(() {
                    _filterStatus = null;
                    _filterTier = null;
                  });
                },
                icon: const Icon(Icons.clear),
                label: const Text('Clear Filters'),
              ),
            ],
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredLandlords.length,
      itemBuilder: (context, index) {
        final landlord = filteredLandlords[index];
        return _buildLandlordCard(landlord);
      },
    );
  }

  Widget _buildLandlordCard(User landlord) {
    Color statusColor;
    IconData statusIcon;
    String statusText;

    switch (landlord.applicationStatus ?? ApplicationStatus.pending) {
      case ApplicationStatus.pending:
        statusColor = kPrimaryOrange;
        statusIcon = Icons.pending;
        statusText = 'Pending';
        break;
      case ApplicationStatus.underReview:
        statusColor = Colors.blue;
        statusIcon = Icons.rate_review;
        statusText = 'Under Review';
        break;
      case ApplicationStatus.approved:
        statusColor = Colors.green;
        statusIcon = Icons.check_circle;
        statusText = 'Approved';
        break;
      case ApplicationStatus.rejected:
        statusColor = kPrimaryRed;
        statusIcon = Icons.cancel;
        statusText = 'Rejected';
        break;
    }

    final tier = landlord.landlordTier ?? LandlordTier.small;
    Color tierColor;
    switch (tier) {
      case LandlordTier.small:
        tierColor = Colors.blue;
        break;
      case LandlordTier.medium:
        tierColor = Colors.purple;
        break;
      case LandlordTier.large:
        tierColor = Colors.amber.shade700;
        break;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: () {
          _showLandlordDetails(landlord);
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 30,
                    backgroundColor: statusColor.withAlpha((255 * 0.2).round()),
                    child: Icon(Icons.person, color: statusColor, size: 32),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          landlord.name,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: kTextColorBlack,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          landlord.email,
                          style: TextStyle(
                            fontSize: 14,
                            color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: statusColor.withAlpha((255 * 0.1).round()),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: statusColor.withAlpha((255 * 0.3).round())),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(statusIcon, color: statusColor, size: 16),
                        const SizedBox(width: 4),
                        Text(
                          statusText,
                          style: TextStyle(
                            color: statusColor,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Divider(height: 1),
              const SizedBox(height: 12),
              Row(
                children: [
                  _buildInfoChip(
                    Icons.business,
                    tier.displayName,
                    tierColor,
                  ),
                  const SizedBox(width: 12),
                  _buildInfoChip(
                    Icons.home_work,
                    '${landlord.propertyCount} properties',
                    Colors.green,
                  ),
                  if (landlord.approvedDate != null) ...[
                    const SizedBox(width: 12),
                    _buildInfoChip(
                      Icons.calendar_today,
                      'Since ${_formatDate(landlord.approvedDate!)}',
                      Colors.blue,
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String label, Color color) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: color),
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

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            title: const Text('Filter Landlords'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Status',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  children: [
                    FilterChip(
                      label: const Text('All'),
                      selected: _filterStatus == null,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterStatus = null;
                        });
                      },
                    ),
                    FilterChip(
                      label: const Text('Pending'),
                      selected: _filterStatus == ApplicationStatus.pending,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterStatus = selected ? ApplicationStatus.pending : null;
                        });
                      },
                    ),
                    FilterChip(
                      label: const Text('Approved'),
                      selected: _filterStatus == ApplicationStatus.approved,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterStatus = selected ? ApplicationStatus.approved : null;
                        });
                      },
                    ),
                    FilterChip(
                      label: const Text('Rejected'),
                      selected: _filterStatus == ApplicationStatus.rejected,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterStatus = selected ? ApplicationStatus.rejected : null;
                        });
                      },
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Text(
                  'Tier',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  children: [
                    FilterChip(
                      label: const Text('All'),
                      selected: _filterTier == null,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterTier = null;
                        });
                      },
                    ),
                    FilterChip(
                      label: const Text('Small'),
                      selected: _filterTier == LandlordTier.small,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterTier = selected ? LandlordTier.small : null;
                        });
                      },
                    ),
                    FilterChip(
                      label: const Text('Medium'),
                      selected: _filterTier == LandlordTier.medium,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterTier = selected ? LandlordTier.medium : null;
                        });
                      },
                    ),
                    FilterChip(
                      label: const Text('Large'),
                      selected: _filterTier == LandlordTier.large,
                      onSelected: (selected) {
                        setDialogState(() {
                          _filterTier = selected ? LandlordTier.large : null;
                        });
                      },
                    ),
                  ],
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
                  setDialogState(() {
                    _filterStatus = null;
                    _filterTier = null;
                  });
                },
                child: const Text('Clear'),
              ),
              ElevatedButton(
                onPressed: () {
                  setState(() {
                    // Apply filters
                  });
                  Navigator.pop(context);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimaryRed,
                  foregroundColor: Colors.white,
                ),
                child: const Text('Apply'),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showLandlordDetails(User landlord) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.7,
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
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 40,
                        backgroundColor: kPrimaryRed.withAlpha((255 * 0.1).round()),
                        child: const Icon(Icons.person, color: kPrimaryRed, size: 40),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              landlord.name,
                              style: const TextStyle(
                                fontSize: 22,
                                fontWeight: FontWeight.bold,
                                color: kTextColorBlack,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              landlord.email,
                              style: TextStyle(
                                fontSize: 14,
                                color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  _buildDetailRow('Tier', landlord.landlordTier?.displayName ?? 'N/A'),
                  _buildDetailRow('Properties', '${landlord.propertyCount}'),
                  _buildDetailRow('Status', landlord.applicationStatus?.displayName ?? 'N/A'),
                  if (landlord.approvedDate != null)
                    _buildDetailRow('Approved On', _formatDate(landlord.approvedDate!)),
                  const SizedBox(height: 24),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            Navigator.pop(context);
                            // TODO: Navigate to landlord's properties
                          },
                          icon: const Icon(Icons.home_work, size: 18),
                          label: const Text('View Properties'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: kPrimaryRed,
                            side: const BorderSide(color: kPrimaryRed),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () {
                            Navigator.pop(context);
                            // TODO: Navigate to landlord application
                          },
                          icon: const Icon(Icons.description, size: 18),
                          label: const Text('View Application'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: kPrimaryRed,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 14,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: kTextColorBlack,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
