import 'package:flutter/material.dart';
import 'package:rental_app/models/rental_history.dart';
import 'package:rental_app/utils/constants.dart';

class LandlordRentalHistoryScreen extends StatefulWidget {
  const LandlordRentalHistoryScreen({super.key});

  @override
  State<LandlordRentalHistoryScreen> createState() => _LandlordRentalHistoryScreenState();
}

class _LandlordRentalHistoryScreenState extends State<LandlordRentalHistoryScreen> {
  RentalStatus _filterStatus = RentalStatus.active;
  
  // TODO: Replace with actual data
  final List<RentalHistory> _mockHistory = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rental History'),
        actions: [
          PopupMenuButton<RentalStatus>(
            icon: const Icon(Icons.filter_list),
            initialValue: _filterStatus,
            onSelected: (status) {
              setState(() {
                _filterStatus = status;
              });
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: RentalStatus.active,
                child: Text(RentalStatus.active.displayName),
              ),
              PopupMenuItem(
                value: RentalStatus.completed,
                child: Text(RentalStatus.completed.displayName),
              ),
              PopupMenuItem(
                value: RentalStatus.terminated,
                child: Text(RentalStatus.terminated.displayName),
              ),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          _buildRevenueOverview(),
          Expanded(child: _buildRentalHistoryList()),
        ],
      ),
    );
  }

  Widget _buildRevenueOverview() {
    // TODO: Calculate from actual data
    final totalRevenue = 125000.0;
    final monthlyRevenue = 42500.0;
    final activeRentals = 3;

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
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: _buildRevenueCard(
                  'Total Revenue',
                  'K${(totalRevenue / 1000).toStringAsFixed(1)}k',
                  Icons.attach_money,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildRevenueCard(
                  'Monthly',
                  'K${(monthlyRevenue / 1000).toStringAsFixed(1)}k',
                  Icons.calendar_month,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _buildRevenueCard(
            'Active Rentals',
            activeRentals.toString(),
            Icons.home_work,
          ),
        ],
      ),
    );
  }

  Widget _buildRevenueCard(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white.withAlpha((255 * 0.2).round()),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(icon, color: Colors.white, size: 24),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 12,
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

  Widget _buildRentalHistoryList() {
    final filteredHistory = _mockHistory
        .where((rental) => rental.status == _filterStatus)
        .toList();

    if (filteredHistory.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.history_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              'No ${_filterStatus.displayName.toLowerCase()} rentals',
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
      itemCount: filteredHistory.length,
      itemBuilder: (context, index) {
        final rental = filteredHistory[index];
        return _buildRentalCard(rental);
      },
    );
  }

  Widget _buildRentalCard(RentalHistory rental) {
    Color statusColor;
    IconData statusIcon;

    switch (rental.status) {
      case RentalStatus.active:
        statusColor = Colors.green;
        statusIcon = Icons.check_circle;
        break;
      case RentalStatus.completed:
        statusColor = Colors.blue;
        statusIcon = Icons.done_all;
        break;
      case RentalStatus.terminated:
        statusColor = kPrimaryRed;
        statusIcon = Icons.cancel;
        break;
    }

    final duration = rental.endDate != null
        ? rental.endDate!.difference(rental.startDate).inDays
        : DateTime.now().difference(rental.startDate).inDays;
    final months = (duration / 30).floor();

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
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        rental.propertyTitle,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: kTextColorBlack,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.person, size: 16, color: kTextColorGrey),
                          const SizedBox(width: 4),
                          Text(
                            rental.tenantName,
                            style: TextStyle(
                              fontSize: 14,
                              color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                            ),
                          ),
                        ],
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
                        rental.status.displayName,
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
                Expanded(
                  child: _buildInfoItem(
                    Icons.calendar_today,
                    'Start Date',
                    _formatDate(rental.startDate),
                  ),
                ),
                Expanded(
                  child: _buildInfoItem(
                    Icons.event,
                    'End Date',
                    rental.endDate != null ? _formatDate(rental.endDate!) : 'Ongoing',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _buildInfoItem(
                    Icons.access_time,
                    'Duration',
                    '$months months',
                  ),
                ),
                Expanded(
                  child: _buildInfoItem(
                    Icons.attach_money,
                    'Monthly Rent',
                    'K${rental.monthlyRent.toStringAsFixed(0)}',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.green.withAlpha((255 * 0.1).round()),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  const Icon(Icons.payments, color: Colors.green, size: 20),
                  const SizedBox(width: 12),
                  Text(
                    'Total Earned: K${(rental.monthlyRent * months).toStringAsFixed(0)}',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.green,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoItem(IconData icon, String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 16, color: kTextColorGrey),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w500,
            color: kTextColorBlack,
          ),
        ),
      ],
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
