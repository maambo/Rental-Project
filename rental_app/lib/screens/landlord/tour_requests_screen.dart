import 'package:flutter/material.dart';
import 'package:rental_app/models/tour_request.dart';
import 'package:rental_app/utils/constants.dart';

class TourRequestsScreen extends StatefulWidget {
  const TourRequestsScreen({super.key});

  @override
  State<TourRequestsScreen> createState() => _TourRequestsScreenState();
}

class _TourRequestsScreenState extends State<TourRequestsScreen> {
  TourStatus _filterStatus = TourStatus.pending;
  
  // TODO: Replace with actual data
  final List<TourRequest> _mockTourRequests = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tour Requests'),
        actions: [
          PopupMenuButton<TourStatus>(
            icon: const Icon(Icons.filter_list),
            initialValue: _filterStatus,
            onSelected: (status) {
              setState(() {
                _filterStatus = status;
              });
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: TourStatus.pending,
                child: Text(TourStatus.pending.displayName),
              ),
              PopupMenuItem(
                value: TourStatus.approved,
                child: Text(TourStatus.approved.displayName),
              ),
              PopupMenuItem(
                value: TourStatus.declined,
                child: Text(TourStatus.declined.displayName),
              ),
              PopupMenuItem(
                value: TourStatus.completed,
                child: Text(TourStatus.completed.displayName),
              ),
            ],
          ),
        ],
      ),
      body: _buildTourRequestsList(),
    );
  }

  Widget _buildTourRequestsList() {
    final filteredRequests = _mockTourRequests
        .where((request) => request.status == _filterStatus)
        .toList();

    if (filteredRequests.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.tour_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              'No ${_filterStatus.displayName.toLowerCase()} tour requests',
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
      itemCount: filteredRequests.length,
      itemBuilder: (context, index) {
        final request = filteredRequests[index];
        return _buildTourRequestCard(request);
      },
    );
  }

  Widget _buildTourRequestCard(TourRequest request) {
    Color statusColor;
    IconData statusIcon;

    switch (request.status) {
      case TourStatus.pending:
        statusColor = kPrimaryOrange;
        statusIcon = Icons.pending;
        break;
      case TourStatus.approved:
        statusColor = Colors.green;
        statusIcon = Icons.check_circle;
        break;
      case TourStatus.declined:
        statusColor = kPrimaryRed;
        statusIcon = Icons.cancel;
        break;
      case TourStatus.completed:
        statusColor = Colors.blue;
        statusIcon = Icons.done_all;
        break;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                        request.propertyTitle,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: kTextColorBlack,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        request.status.displayName,
                        style: TextStyle(
                          fontSize: 12,
                          color: statusColor,
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
            const SizedBox(height: 16),
            _buildInfoRow(Icons.person, 'Name', request.userName),
            const SizedBox(height: 12),
            _buildInfoRow(Icons.email, 'Email', request.email),
            const SizedBox(height: 12),
            _buildInfoRow(Icons.phone, 'Phone', request.phone),
            const SizedBox(height: 12),
            _buildInfoRow(
              Icons.calendar_today,
              'Preferred Date',
              _formatDate(request.preferredDate),
            ),
            if (request.additionalNotes != null) ...[
              const SizedBox(height: 12),
              _buildInfoRow(Icons.note, 'Notes', request.additionalNotes!),
            ],
            if (request.landlordFeedback != null) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.withAlpha((255 * 0.1).round()),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.feedback, size: 16, color: Colors.blue),
                        SizedBox(width: 8),
                        Text(
                          'Your Feedback',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Colors.blue,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      request.landlordFeedback!,
                      style: const TextStyle(fontSize: 14),
                    ),
                  ],
                ),
              ),
            ],
            if (request.status == TourStatus.pending) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _showDeclineDialog(request),
                      icon: const Icon(Icons.close, size: 18),
                      label: const Text('Decline'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: kPrimaryRed,
                        side: const BorderSide(color: kPrimaryRed),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => _showApproveDialog(request),
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
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: kTextColorGrey),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: kTextColorBlack,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  void _showApproveDialog(TourRequest request) {
    final TextEditingController feedbackController = TextEditingController();
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Approve Tour Request'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Approve tour for ${request.userName}?'),
            const SizedBox(height: 16),
            TextField(
              controller: feedbackController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Feedback (Optional)',
                hintText: 'Add any additional information or instructions...',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.withAlpha((255 * 0.1).round()),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                children: [
                  Icon(Icons.email, size: 16, color: Colors.blue),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Email notification will be sent to the requester',
                      style: TextStyle(fontSize: 12, color: Colors.blue),
                    ),
                  ),
                ],
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
              // TODO: Implement approve with email notification
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Tour approved and email sent'),
                  backgroundColor: Colors.green,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
            ),
            child: const Text('Approve & Send Email'),
          ),
        ],
      ),
    );
  }

  void _showDeclineDialog(TourRequest request) {
    final TextEditingController reasonController = TextEditingController();
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Decline Tour Request'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Decline tour for ${request.userName}?'),
            const SizedBox(height: 16),
            TextField(
              controller: reasonController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Reason (Optional)',
                hintText: 'Let them know why...',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.withAlpha((255 * 0.1).round()),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                children: [
                  Icon(Icons.email, size: 16, color: Colors.blue),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Email notification will be sent to the requester',
                      style: TextStyle(fontSize: 12, color: Colors.blue),
                    ),
                  ),
                ],
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
              // TODO: Implement decline with email notification
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Tour declined and email sent'),
                  backgroundColor: kPrimaryRed,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: kPrimaryRed,
              foregroundColor: Colors.white,
            ),
            child: const Text('Decline & Send Email'),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year} at ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
  }
}
