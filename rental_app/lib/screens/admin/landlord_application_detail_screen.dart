import 'package:flutter/material.dart';
import 'package:rental_app/models/landlord_application.dart';
import 'package:rental_app/models/application_status.dart';
import 'package:rental_app/models/landlord_tier.dart';
import 'package:rental_app/utils/constants.dart';

class LandlordApplicationDetailScreen extends StatefulWidget {
  final LandlordApplication application;

  const LandlordApplicationDetailScreen({
    super.key,
    required this.application,
  });

  @override
  State<LandlordApplicationDetailScreen> createState() => _LandlordApplicationDetailScreenState();
}

class _LandlordApplicationDetailScreenState extends State<LandlordApplicationDetailScreen> {
  final TextEditingController _notesController = TextEditingController();
  final TextEditingController _rejectionReasonController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _notesController.text = widget.application.adminNotes ?? '';
    _rejectionReasonController.text = widget.application.rejectionReason ?? '';
  }

  @override
  void dispose() {
    _notesController.dispose();
    _rejectionReasonController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Application Details'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildStatusBanner(),
            const SizedBox(height: 24),
            _buildApplicantInfo(),
            const SizedBox(height: 24),
            _buildTierInfo(),
            const SizedBox(height: 24),
            _buildDocumentsSection(),
            const SizedBox(height: 24),
            _buildAdminNotesSection(),
            if (widget.application.status == ApplicationStatus.pending ||
                widget.application.status == ApplicationStatus.underReview) ...[
              const SizedBox(height: 24),
              _buildActionButtons(),
            ],
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusBanner() {
    Color statusColor;
    IconData statusIcon;

    switch (widget.application.status) {
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

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: statusColor.withAlpha((255 * 0.1).round()),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: statusColor.withAlpha((255 * 0.3).round())),
      ),
      child: Row(
        children: [
          Icon(statusIcon, color: statusColor, size: 32),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.application.status.displayName,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Submitted on ${_formatDate(widget.application.submittedDate)}',
                  style: TextStyle(
                    fontSize: 14,
                    color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                  ),
                ),
                if (widget.application.reviewedDate != null)
                  Text(
                    'Reviewed on ${_formatDate(widget.application.reviewedDate!)}',
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
    );
  }

  Widget _buildApplicantInfo() {
    return _buildSection(
      'Applicant Information',
      Icons.person_outline,
      Column(
        children: [
          _buildInfoRow('Name', widget.application.userName),
          const SizedBox(height: 12),
          _buildInfoRow('Email', widget.application.email),
          const SizedBox(height: 12),
          _buildInfoRow('User ID', widget.application.userId),
        ],
      ),
    );
  }

  Widget _buildTierInfo() {
    return _buildSection(
      'Tier Information',
      Icons.business_outlined,
      Column(
        children: [
          _buildInfoRow('Selected Tier', widget.application.tier.displayName),
          const SizedBox(height: 12),
          _buildInfoRow(
            'Property Limit',
            widget.application.tier.propertyLimit == -1
                ? 'Unlimited'
                : '${widget.application.tier.propertyLimit} properties',
          ),
          const SizedBox(height: 12),
          _buildInfoRow(
            'Application Fee',
            'K${widget.application.applicationFee.toStringAsFixed(2)}',
          ),
        ],
      ),
    );
  }

  Widget _buildDocumentsSection() {
    return _buildSection(
      'Uploaded Documents',
      Icons.folder_outlined,
      widget.application.documents.isEmpty
          ? Text(
              'No documents uploaded',
              style: TextStyle(
                color: kTextColorGrey.withAlpha((255 * 0.6).round()),
                fontStyle: FontStyle.italic,
              ),
            )
          : Column(
              children: widget.application.documents.map((doc) {
                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: kBackgroundColorLight,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: kTextColorGrey.withAlpha((255 * 0.2).round()),
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.insert_drive_file, size: 20, color: kPrimaryRed),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          doc,
                          style: const TextStyle(fontSize: 14),
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.download, size: 20),
                        onPressed: () {
                          // TODO: Implement document download
                        },
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
    );
  }

  Widget _buildAdminNotesSection() {
    return _buildSection(
      'Admin Notes',
      Icons.note_outlined,
      Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextField(
            controller: _notesController,
            maxLines: 4,
            decoration: InputDecoration(
              hintText: 'Add notes about this application...',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
              filled: true,
              fillColor: kBackgroundColorLight,
            ),
          ),
          if (widget.application.status == ApplicationStatus.rejected) ...[
            const SizedBox(height: 16),
            const Text(
              'Rejection Reason',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: kTextColorBlack,
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _rejectionReasonController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Reason for rejection...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                filled: true,
                fillColor: kPrimaryRed.withAlpha((255 * 0.05).round()),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Column(
      children: [
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _showApproveDialog,
            icon: const Icon(Icons.check_circle),
            label: const Text('Approve Application'),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: _showRejectDialog,
            icon: const Icon(Icons.cancel),
            label: const Text('Reject Application'),
            style: OutlinedButton.styleFrom(
              foregroundColor: kPrimaryRed,
              side: const BorderSide(color: kPrimaryRed),
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildSection(String title, IconData icon, Widget content) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha((255 * 0.05).round()),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: kPrimaryRed, size: 24),
              const SizedBox(width: 12),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: kTextColorBlack,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          content,
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Row(
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
    );
  }

  void _showApproveDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Approve Application'),
        content: Text(
          'Are you sure you want to approve ${widget.application.userName}\'s application for ${widget.application.tier.displayName} tier?',
        ),
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
                  content: Text('Application approved successfully'),
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

  void _showRejectDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Reject Application'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Are you sure you want to reject ${widget.application.userName}\'s application?',
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _rejectionReasonController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Rejection Reason (Required)',
                hintText: 'Please provide a reason for rejection...',
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
              if (_rejectionReasonController.text.trim().isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Please provide a rejection reason'),
                    backgroundColor: kPrimaryRed,
                  ),
                );
                return;
              }
              // TODO: Implement reject logic
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Application rejected'),
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

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
