import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class TenantRentalsScreen extends StatefulWidget {
  const TenantRentalsScreen({super.key});

  @override
  State<TenantRentalsScreen> createState() => _TenantRentalsScreenState();
}

class _TenantRentalsScreenState extends State<TenantRentalsScreen> {
  String _filter = 'Active'; // Active, Past

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Rentals'),
        actions: [
          PopupMenuButton<String>(
            initialValue: _filter,
            onSelected: (value) {
              setState(() {
                _filter = value;
              });
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'Active', child: Text('Active Rentals')),
              const PopupMenuItem(value: 'Past', child: Text('Past Rentals')),
            ],
          ),
        ],
      ),
      body: _buildRentalsList(),
    );
  }

  Widget _buildRentalsList() {
    // TODO: Replace with actual data
    if (_filter == 'Active') {
      return ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildRentalCard(
            'Modern Downtown Apartment',
            'PIV0001',
            'John Smith',
            'john.smith@email.com',
            '+260 977 123 456',
            'Jan 1, 2024',
            'Dec 31, 2024',
            'K3,500',
            'Active',
            Colors.green,
          ),
          _buildRentalCard(
            'Cozy Garden House',
            'PIV0002',
            'Jane Doe',
            'jane.doe@email.com',
            '+260 966 789 012',
            'Mar 15, 2024',
            'Mar 14, 2025',
            'K4,200',
            'Active',
            Colors.green,
          ),
        ],
      );
    } else {
      return ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildRentalCard(
            'Suburban Family Home',
            'PIV0003',
            'Bob Johnson',
            'bob.j@email.com',
            '+260 955 345 678',
            'Jan 1, 2023',
            'Dec 31, 2023',
            'K2,800',
            'Completed',
            Colors.grey,
          ),
        ],
      );
    }
  }

  Widget _buildRentalCard(
    String propertyTitle,
    String propertyCode,
    String landlordName,
    String landlordEmail,
    String landlordPhone,
    String startDate,
    String endDate,
    String monthlyRent,
    String status,
    Color statusColor,
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
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    propertyTitle,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: kTextColorBlack,
                    ),
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
            const SizedBox(height: 4),
            Text(
              'Property Code: $propertyCode',
              style: TextStyle(
                fontSize: 12,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
            const SizedBox(height: 16),
            const Divider(height: 1),
            const SizedBox(height: 16),
            _buildInfoRow(Icons.person, 'Landlord', landlordName),
            const SizedBox(height: 8),
            _buildInfoRow(Icons.email, 'Email', landlordEmail),
            const SizedBox(height: 8),
            _buildInfoRow(Icons.phone, 'Phone', landlordPhone),
            const SizedBox(height: 16),
            const Divider(height: 1),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _buildDateInfo('Start Date', startDate),
                ),
                Expanded(
                  child: _buildDateInfo('End Date', endDate),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _buildMonthlyRent(monthlyRent),
                ),
                if (status == 'Active') ...[
                  const SizedBox(width: 12),
                  ElevatedButton.icon(
                    onPressed: () {
                      // TODO: Navigate to messaging
                    },
                    icon: const Icon(Icons.message, size: 18),
                    label: const Text('Message'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kPrimaryRed,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 16, color: kTextColorGrey),
        const SizedBox(width: 8),
        Text(
          '$label: ',
          style: TextStyle(
            fontSize: 14,
            color: kTextColorGrey.withAlpha((255 * 0.8).round()),
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

  Widget _buildDateInfo(String label, String date) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: kTextColorGrey.withAlpha((255 * 0.7).round()),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          date,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: kTextColorBlack,
          ),
        ),
      ],
    );
  }

  Widget _buildMonthlyRent(String rent) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.green.withAlpha((255 * 0.1).round()),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Monthly Rent',
            style: TextStyle(
              fontSize: 12,
              color: kTextColorGrey.withAlpha((255 * 0.7).round()),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            rent,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.green,
            ),
          ),
        ],
      ),
    );
  }
}
