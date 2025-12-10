import 'package:flutter/material.dart';
import 'package:rental_app/models/maintenance_plan.dart';
import 'package:rental_app/utils/constants.dart';

class MaintenancePlansScreen extends StatefulWidget {
  const MaintenancePlansScreen({super.key});

  @override
  State<MaintenancePlansScreen> createState() => _MaintenancePlansScreenState();
}

class _MaintenancePlansScreenState extends State<MaintenancePlansScreen> {
  MaintenanceStatus _filterStatus = MaintenanceStatus.scheduled;
  
  // TODO: Replace with actual data
  final List<MaintenancePlan> _mockPlans = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Maintenance Plans'),
        actions: [
          PopupMenuButton<MaintenanceStatus>(
            icon: const Icon(Icons.filter_list),
            initialValue: _filterStatus,
            onSelected: (status) {
              setState(() {
                _filterStatus = status;
              });
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: MaintenanceStatus.scheduled,
                child: Text(MaintenanceStatus.scheduled.displayName),
              ),
              PopupMenuItem(
                value: MaintenanceStatus.inProgress,
                child: Text(MaintenanceStatus.inProgress.displayName),
              ),
              PopupMenuItem(
                value: MaintenanceStatus.completed,
                child: Text(MaintenanceStatus.completed.displayName),
              ),
              PopupMenuItem(
                value: MaintenanceStatus.cancelled,
                child: Text(MaintenanceStatus.cancelled.displayName),
              ),
            ],
          ),
        ],
      ),
      body: _buildMaintenanceList(),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddMaintenanceDialog,
        icon: const Icon(Icons.add),
        label: const Text('Add Plan'),
        backgroundColor: kPrimaryRed,
      ),
    );
  }

  Widget _buildMaintenanceList() {
    final filteredPlans = _mockPlans
        .where((plan) => plan.status == _filterStatus)
        .toList();

    if (filteredPlans.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.build_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              'No ${_filterStatus.displayName.toLowerCase()} maintenance plans',
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
      itemCount: filteredPlans.length,
      itemBuilder: (context, index) {
        final plan = filteredPlans[index];
        return _buildMaintenanceCard(plan);
      },
    );
  }

  Widget _buildMaintenanceCard(MaintenancePlan plan) {
    Color statusColor;
    IconData statusIcon;

    switch (plan.status) {
      case MaintenanceStatus.scheduled:
        statusColor = kPrimaryOrange;
        statusIcon = Icons.schedule;
        break;
      case MaintenanceStatus.inProgress:
        statusColor = Colors.blue;
        statusIcon = Icons.build;
        break;
      case MaintenanceStatus.completed:
        statusColor = Colors.green;
        statusIcon = Icons.check_circle;
        break;
      case MaintenanceStatus.cancelled:
        statusColor = kTextColorGrey;
        statusIcon = Icons.cancel;
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
                        plan.title,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: kTextColorBlack,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        plan.propertyTitle,
                        style: TextStyle(
                          fontSize: 12,
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
                    plan.status.displayName,
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
            Text(
              plan.description,
              style: TextStyle(
                fontSize: 14,
                color: kTextColorGrey.withAlpha((255 * 0.9).round()),
                height: 1.5,
              ),
            ),
            const SizedBox(height: 16),
            const Divider(height: 1),
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(Icons.calendar_today, size: 16, color: kTextColorGrey),
                const SizedBox(width: 8),
                Text(
                  'Scheduled: ${_formatDate(plan.scheduledDate)}',
                  style: const TextStyle(fontSize: 14),
                ),
                const Spacer(),
                if (plan.cost != null)
                  Text(
                    'K${plan.cost!.toStringAsFixed(0)}',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: kPrimaryRed,
                    ),
                  ),
              ],
            ),
            if (plan.status == MaintenanceStatus.scheduled) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _updateStatus(plan, MaintenanceStatus.inProgress),
                      icon: const Icon(Icons.play_arrow, size: 18),
                      label: const Text('Start'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Colors.blue,
                        side: const BorderSide(color: Colors.blue),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _updateStatus(plan, MaintenanceStatus.cancelled),
                      icon: const Icon(Icons.close, size: 18),
                      label: const Text('Cancel'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: kTextColorGrey,
                        side: BorderSide(color: kTextColorGrey.withAlpha((255 * 0.5).round())),
                      ),
                    ),
                  ),
                ],
              ),
            ],
            if (plan.status == MaintenanceStatus.inProgress) ...[
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () => _updateStatus(plan, MaintenanceStatus.completed),
                  icon: const Icon(Icons.check, size: 18),
                  label: const Text('Mark as Completed'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  void _showAddMaintenanceDialog() {
    final TextEditingController titleController = TextEditingController();
    final TextEditingController descriptionController = TextEditingController();
    final TextEditingController costController = TextEditingController();
    DateTime selectedDate = DateTime.now().add(const Duration(days: 1));
    String? selectedProperty;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            title: const Text('Add Maintenance Plan'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  DropdownButtonFormField<String>(
                    decoration: const InputDecoration(
                      labelText: 'Property',
                      border: OutlineInputBorder(),
                    ),
                    value: selectedProperty,
                    items: const [
                      // TODO: Load from actual properties
                      DropdownMenuItem(value: 'prop1', child: Text('Property 1')),
                      DropdownMenuItem(value: 'prop2', child: Text('Property 2')),
                    ],
                    onChanged: (value) {
                      setDialogState(() {
                        selectedProperty = value;
                      });
                    },
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: titleController,
                    decoration: const InputDecoration(
                      labelText: 'Title',
                      hintText: 'e.g., Plumbing Repair',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: descriptionController,
                    maxLines: 3,
                    decoration: const InputDecoration(
                      labelText: 'Description',
                      hintText: 'Describe the maintenance work...',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: costController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Estimated Cost (Optional)',
                      hintText: 'ZMW',
                      prefixText: 'K ',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 16),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Scheduled Date'),
                    subtitle: Text(_formatDate(selectedDate)),
                    trailing: const Icon(Icons.calendar_today),
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: selectedDate,
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (date != null) {
                        setDialogState(() {
                          selectedDate = date;
                        });
                      }
                    },
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
                            'Tenants will be notified via email',
                            style: TextStyle(fontSize: 12, color: Colors.blue),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Cancel'),
              ),
              ElevatedButton(
                onPressed: () {
                  if (titleController.text.isEmpty || selectedProperty == null) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Please fill in all required fields'),
                        backgroundColor: kPrimaryRed,
                      ),
                    );
                    return;
                  }
                  // TODO: Implement add maintenance plan with email notification
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Maintenance plan created and tenants notified'),
                      backgroundColor: Colors.green,
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimaryRed,
                  foregroundColor: Colors.white,
                ),
                child: const Text('Create & Notify'),
              ),
            ],
          );
        },
      ),
    );
  }

  void _updateStatus(MaintenancePlan plan, MaintenanceStatus newStatus) {
    // TODO: Implement status update with email notification
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Status updated to ${newStatus.displayName}'),
        backgroundColor: Colors.green,
      ),
    );
    setState(() {
      // Update would happen here
    });
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
