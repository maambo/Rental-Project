import 'package:flutter/material.dart';
import 'package:rental_app/models/landlord_application.dart';
import 'package:rental_app/models/application_status.dart';
import 'package:rental_app/models/landlord_tier.dart';
import 'package:rental_app/utils/constants.dart';

class LandlordApplicationsScreen extends StatefulWidget {
  const LandlordApplicationsScreen({super.key});

  @override
  State<LandlordApplicationsScreen> createState() => _LandlordApplicationsScreenState();
}

class _LandlordApplicationsScreenState extends State<LandlordApplicationsScreen> {
  ApplicationStatus? _filterStatus;
  
  // TODO: Replace with actual service call
  final List<LandlordApplication> _mockApplications = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Landlord Applications'),
        actions: [
          PopupMenuButton<ApplicationStatus?>(
            icon: const Icon(Icons.filter_list),
            onSelected: (status) {
              setState(() {
                _filterStatus = status;
              });
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: null,
                child: Text('All Applications'),
              ),
              PopupMenuItem(
                value: ApplicationStatus.pending,
                child: Text(ApplicationStatus.pending.displayName),
              ),
              PopupMenuItem(
                value: ApplicationStatus.underReview,
                child: Text(ApplicationStatus.underReview.displayName),
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
      body: _buildApplicationsList(),
    );
  }

  Widget _buildApplicationsList() {
    final filteredApplications = _filterStatus == null
        ? _mockApplications
        : _mockApplications.where((app) => app.status == _filterStatus).toList();

    if (filteredApplications.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.assignment_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              _filterStatus == null
                  ? 'No applications yet'
                  : 'No ${_filterStatus!.displayName.toLowerCase()} applications',
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
      itemCount: filteredApplications.length,
      itemBuilder: (context, index) {
        final application = filteredApplications[index];
        return _buildApplicationCard(application);
      },
    );
  }

  Widget _buildApplicationCard(LandlordApplication application) {
    Color statusColor;
    IconData statusIcon;

    switch (application.status) {
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
      child: InkWell(
        onTap: () {
          Navigator.pushNamed(
            context,
            '/admin/application-detail',
            arguments: application,
          );
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: statusColor.withAlpha((255 * 0.1).round()),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(statusIcon, color: statusColor, size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          application.userName,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: kTextColorBlack,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          application.email,
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
                    child: Text(
                      application.status.displayName,
                      style: TextStyle(
                        color: statusColor,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
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
                    Icons.business_outlined,
                    application.tier.displayName,
                    Colors.purple,
                  ),
                  const SizedBox(width: 12),
                  _buildInfoChip(
                    Icons.attach_money,
                    'K${application.applicationFee.toStringAsFixed(0)}',
                    Colors.green,
                  ),
                  const SizedBox(width: 12),
                  _buildInfoChip(
                    Icons.calendar_today_outlined,
                    _formatDate(application.submittedDate),
                    Colors.blue,
                  ),
                ],
              ),
              if (application.status == ApplicationStatus.pending) ...[
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          // TODO: Implement reject
                        },
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
                        onPressed: () {
                          // TODO: Implement approve
                        },
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

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
