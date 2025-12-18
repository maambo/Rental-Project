import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';
import 'package:rental_app/widgets/dashboard_stat_card.dart';

class RevenueReportScreen extends StatefulWidget {
  const RevenueReportScreen({super.key});

  @override
  State<RevenueReportScreen> createState() => _RevenueReportScreenState();
}

class _RevenueReportScreenState extends State<RevenueReportScreen> {
  String _selectedPeriod = 'This Month';
  
  // Mock Data
  final Map<String, dynamic> _stats = {
    'totalRevenue': 125400.0,
    'commission': 12540.0,
    'pendingPayouts': 4500.0,
    'transactions': 142,
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Revenue & Finance'),
        actions: [
          PopupMenuButton<String>(
            initialValue: _selectedPeriod,
            onSelected: (value) {
              setState(() {
                _selectedPeriod = value;
              });
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'This Week', child: Text('This Week')),
              const PopupMenuItem(value: 'This Month', child: Text('This Month')),
              const PopupMenuItem(value: 'Last Month', child: Text('Last Month')),
              const PopupMenuItem(value: 'This Year', child: Text('This Year')),
              const PopupMenuItem(value: 'All Time', child: Text('All Time')),
            ],
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  Text(_selectedPeriod, style: const TextStyle(fontWeight: FontWeight.bold)),
                  const Icon(Icons.arrow_drop_down),
                ],
              ),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Total Revenue Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: kPrimaryGradient,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: kPrimaryRed.withOpacity(0.3),
                    blurRadius: 15,
                    offset: const Offset(0, 5),
                  ),
                ],
              ),
              child: Column(
                children: [
                  const Text(
                    'Total Transaction Volume',
                    style: TextStyle(color: Colors.white70, fontSize: 16),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'K${_stats['totalRevenue'].toStringAsFixed(2)}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 36,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      '+15.3% vs last period',
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            
            const Text(
              'Breakdown',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),

            Row(
              children: [
                Expanded(
                  child: DashboardStatCard(
                    title: 'Platform Commission',
                    value: 'K${_stats['commission'].toStringAsFixed(0)}',
                    icon: Icons.monetization_on,
                    color: Colors.green,
                    trend: '10% of volume',
                    trendUp: true,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: DashboardStatCard(
                    title: 'Pending Payouts',
                    value: 'K${_stats['pendingPayouts'].toStringAsFixed(0)}',
                    icon: Icons.pending_actions,
                    color: kPrimaryOrange,
                    trend: 'Next payout: Fri',
                    trendUp: true,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),
            const Text(
              'Recent Transactions',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            
            ListView.separated(
              physics: const NeverScrollableScrollPhysics(),
              shrinkWrap: true,
              itemCount: 5,
              separatorBuilder: (context, index) => const Divider(),
              itemBuilder: (context, index) {
                return ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: CircleAvatar(
                    backgroundColor: Colors.blue.withOpacity(0.1),
                    child: const Icon(Icons.receipt_long, color: Colors.blue),
                  ),
                  title: Text('Rent Payment - Unit ${101 + index}'),
                  subtitle: Text('Dec ${10 + index}, 2024 • John Doe'),
                  trailing: Text(
                    '+K3,500.00',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
