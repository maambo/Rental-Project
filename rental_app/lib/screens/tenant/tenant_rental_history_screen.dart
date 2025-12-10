import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class TenantRentalHistoryScreen extends StatefulWidget {
  const TenantRentalHistoryScreen({super.key});

  @override
  State<TenantRentalHistoryScreen> createState() => _TenantRentalHistoryScreenState();
}

class _TenantRentalHistoryScreenState extends State<TenantRentalHistoryScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rental History'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildSummaryCards(),
            const SizedBox(height: 24),
            const Text(
              'Past Rentals',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: kTextColorBlack,
              ),
            ),
            const SizedBox(height: 16),
            _buildHistoryList(),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryCards() {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildSummaryCard(
                'Total Rentals',
                '5',
                Icons.home_work,
                kPrimaryRed,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: _buildSummaryCard(
                'Total Paid',
                'K125k',
                Icons.payment,
                Colors.green,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: _buildSummaryCard(
                'Avg Duration',
                '18 months',
                Icons.calendar_today,
                Colors.blue,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: _buildSummaryCard(
                'Reviews Given',
                '4',
                Icons.star,
                Colors.amber,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildSummaryCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withAlpha((255 * 0.1).round()),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withAlpha((255 * 0.3).round())),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 28),
          const SizedBox(height: 12),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            title,
            style: TextStyle(
              fontSize: 12,
              color: kTextColorGrey.withAlpha((255 * 0.8).round()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHistoryList() {
    // TODO: Replace with actual data
    return Column(
      children: [
        _buildHistoryCard(
          'Suburban Family Home',
          'PIV0003',
          'Jan 1, 2023',
          'Dec 31, 2023',
          '12 months',
          'K2,800',
          'K33,600',
          true,
        ),
        _buildHistoryCard(
          'City Center Studio',
          'PIV0004',
          'Jun 1, 2022',
          'May 31, 2023',
          '12 months',
          'K2,200',
          'K26,400',
          true,
        ),
        _buildHistoryCard(
          'Lakeside Cottage',
          'PIV0005',
          'Mar 1, 2021',
          'Aug 31, 2022',
          '18 months',
          'K3,000',
          'K54,000',
          false,
        ),
      ],
    );
  }

  Widget _buildHistoryCard(
    String propertyTitle,
    String propertyCode,
    String startDate,
    String endDate,
    String duration,
    String monthlyRent,
    String totalPaid,
    bool hasReview,
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
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        propertyTitle,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: kTextColorBlack,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        propertyCode,
                        style: TextStyle(
                          fontSize: 12,
                          color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                        ),
                      ),
                    ],
                  ),
                ),
                if (hasReview)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.amber.withAlpha((255 * 0.1).round()),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.star, color: Colors.amber, size: 14),
                        SizedBox(width: 4),
                        Text(
                          'Reviewed',
                          style: TextStyle(
                            color: Colors.amber,
                            fontSize: 11,
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
            Row(
              children: [
                Expanded(
                  child: _buildInfoColumn('Start Date', startDate),
                ),
                Expanded(
                  child: _buildInfoColumn('End Date', endDate),
                ),
                Expanded(
                  child: _buildInfoColumn('Duration', duration),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.blue.withAlpha((255 * 0.1).round()),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Monthly Rent',
                          style: TextStyle(
                            fontSize: 11,
                            color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          monthlyRent,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.blue,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.green.withAlpha((255 * 0.1).round()),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Total Paid',
                          style: TextStyle(
                            fontSize: 11,
                            color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          totalPaid,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.green,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoColumn(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            color: kTextColorGrey.withAlpha((255 * 0.7).round()),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: kTextColorBlack,
          ),
        ),
      ],
    );
  }
}
