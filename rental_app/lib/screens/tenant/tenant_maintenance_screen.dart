import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class TenantMaintenanceScreen extends StatefulWidget {
  const TenantMaintenanceScreen({super.key});

  @override
  State<TenantMaintenanceScreen> createState() => _TenantMaintenanceScreenState();
}

class _TenantMaintenanceScreenState extends State<TenantMaintenanceScreen> {
  String _filter = 'Upcoming'; // Upcoming, Completed

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Maintenance Schedule'),
        actions: [
          PopupMenuButton<String>(
            initialValue: _filter,
            onSelected: (value) {
              setState(() {
                _filter = value;
              });
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'Upcoming', child: Text('Upcoming')),
              const PopupMenuItem(value: 'Completed', child: Text('Completed')),
            ],
          ),
        ],
      ),
      body: _buildMaintenanceList(),
    );
  }

  Widget _buildMaintenanceList() {
    // TODO: Replace with actual data
    if (_filter == 'Upcoming') {
      return ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildMaintenanceCard(
            'Plumbing Inspection',
            'Modern Downtown Apartment',
            'Routine inspection of all plumbing fixtures and pipes',
            'Dec 15, 2024',
            '10:00 AM - 12:00 PM',
            'Scheduled',
            Colors.blue,
            Icons.plumbing,
          ),
          _buildMaintenanceCard(
            'HVAC Servicing',
            'Modern Downtown Apartment',
            'Annual HVAC system maintenance and filter replacement',
            'Dec 20, 2024',
            '2:00 PM - 4:00 PM',
            'Scheduled',
            kPrimaryOrange,
            Icons.ac_unit,
          ),
          _buildMaintenanceCard(
            'Electrical Check',
            'Cozy Garden House',
            'Safety inspection of electrical outlets and circuit breakers',
            'Dec 25, 2024',
            '9:00 AM - 11:00 AM',
            'Scheduled',
            Colors.amber,
            Icons.electrical_services,
          ),
        ],
      );
    } else {
      return ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildMaintenanceCard(
            'Water Heater Repair',
            'Modern Downtown Apartment',
            'Fixed water heater thermostat issue',
            'Nov 10, 2024',
            '1:00 PM - 3:00 PM',
            'Completed',
            Colors.green,
            Icons.water_drop,
          ),
          _buildMaintenanceCard(
            'Window Cleaning',
            'Cozy Garden House',
            'Professional window cleaning service',
            'Oct 5, 2024',
            '8:00 AM - 10:00 AM',
            'Completed',
            Colors.green,
            Icons.cleaning_services,
          ),
        ],
      );
    }
  }

  Widget _buildMaintenanceCard(
    String title,
    String property,
    String description,
    String date,
    String time,
    String status,
    Color statusColor,
    IconData icon,
  ) {
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
                  child: Icon(icon, color: statusColor, size: 28),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: kTextColorBlack,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        property,
                        style: TextStyle(
                          fontSize: 13,
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
                    border: Border.all(color: statusColor),
                  ),
                  child: Text(
                    status,
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
              description,
              style: TextStyle(
                fontSize: 14,
                color: kTextColorGrey.withAlpha((255 * 0.9).round()),
              ),
            ),
            const SizedBox(height: 16),
            const Divider(height: 1),
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(
                  Icons.calendar_today,
                  size: 16,
                  color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                ),
                const SizedBox(width: 8),
                Text(
                  date,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: kTextColorBlack,
                  ),
                ),
                const SizedBox(width: 24),
                Icon(
                  Icons.access_time,
                  size: 16,
                  color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                ),
                const SizedBox(width: 8),
                Text(
                  time,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: kTextColorBlack,
                  ),
                ),
              ],
            ),
            if (status == 'Scheduled') ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.withAlpha((255 * 0.05).round()),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: Colors.blue.withAlpha((255 * 0.2).round()),
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline, color: Colors.blue, size: 20),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Please ensure someone is available during the scheduled time',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.blue.shade700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
