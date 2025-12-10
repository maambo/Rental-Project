import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';
import 'package:rental_app/widgets/dashboard_stat_card.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/widgets/global_drawer.dart';

class LandlordDashboardScreen extends StatefulWidget {
  const LandlordDashboardScreen({super.key});

  @override
  State<LandlordDashboardScreen> createState() => _LandlordDashboardScreenState();
}

class _LandlordDashboardScreenState extends State<LandlordDashboardScreen> {
  // TODO: Get from actual user data
  final bool _isApproved = false;
  final String _landlordName = 'John Doe';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Landlord Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {
              // TODO: Navigate to notifications
            },
          ),
        ],
      ),
      drawer: const GlobalDrawer(userRole: UserRole.landlord),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!_isApproved) _buildApprovalBanner(),
            if (!_isApproved) const SizedBox(height: 16),
            _buildWelcomeCard(),
            const SizedBox(height: 24),
            if (_isApproved) ...[
              _buildStatsSection(),
              const SizedBox(height: 24),
              _buildRecentActivity(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildApprovalBanner() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: kPrimaryOrange.withAlpha((255 * 0.1).round()),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: kPrimaryOrange),
      ),
      child: Row(
        children: [
          const Icon(Icons.pending, color: kPrimaryOrange, size: 32),
          const SizedBox(width: 16),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Account Pending Approval',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: kPrimaryOrange,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Your application is under review. You\'ll be notified once approved.',
                  style: TextStyle(fontSize: 14, color: kTextColorBlack),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWelcomeCard() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: kPrimaryGradient,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white.withAlpha((255 * 0.2).round()),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.business, color: Colors.white, size: 32),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Welcome, $_landlordName!',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  _isApproved
                      ? 'Manage your properties and tenants'
                      : 'Your account is pending approval',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsSection() {
    // TODO: Replace with actual data
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Overview',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: kTextColorBlack,
          ),
        ),
        const SizedBox(height: 16),
        DashboardStatCard(
          title: 'My Properties',
          value: '5',
          icon: Icons.home_work,
          color: kPrimaryRed,
          subtitle: '3 occupied',
          trend: '+1 this month',
          trendUp: true,
          onTap: () {
            Navigator.pushNamed(context, '/landlord/properties');
          },
        ),
        const SizedBox(height: 16),
        DashboardStatCard(
          title: 'Pending Tours',
          value: '8',
          icon: Icons.tour_outlined,
          color: kPrimaryOrange,
          subtitle: 'Awaiting response',
          trend: '3 new today',
          trendUp: true,
          onTap: () {
            Navigator.pushNamed(context, '/landlord/tours');
          },
        ),
        const SizedBox(height: 16),
        DashboardStatCard(
          title: 'Monthly Revenue',
          value: 'K42.5k',
          icon: Icons.attach_money,
          color: Colors.green,
          subtitle: 'From 3 properties',
          trend: '+12% vs last month',
          trendUp: true,
          onTap: () {
            Navigator.pushNamed(context, '/landlord/rental-history');
          },
        ),
        const SizedBox(height: 16),
        DashboardStatCard(
          title: 'Unread Messages',
          value: '12',
          icon: Icons.message_outlined,
          color: Colors.blue,
          subtitle: 'From tenants',
          trend: '4 new',
          trendUp: false,
          onTap: () {
            Navigator.pushNamed(context, '/landlord/messages');
          },
        ),
      ],
    );
  }

  Widget _buildRecentActivity() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Recent Activity',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: kTextColorBlack,
          ),
        ),
        const SizedBox(height: 16),
        _buildActivityItem(
          'New tour request',
          'Modern Downtown Apartment',
          Icons.tour,
          kPrimaryOrange,
          '2 hours ago',
        ),
        _buildActivityItem(
          'Property approved',
          'Cozy Garden House',
          Icons.check_circle,
          Colors.green,
          '1 day ago',
        ),
        _buildActivityItem(
          'New message',
          'From John Phiri',
          Icons.message,
          Colors.blue,
          '2 days ago',
        ),
      ],
    );
  }

  Widget _buildActivityItem(
    String title,
    String subtitle,
    IconData icon,
    Color color,
    String time,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
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
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withAlpha((255 * 0.1).round()),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: kTextColorBlack,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 12,
                    color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                  ),
                ),
              ],
            ),
          ),
          Text(
            time,
            style: TextStyle(
              fontSize: 12,
              color: kTextColorGrey.withAlpha((255 * 0.6).round()),
            ),
          ),
        ],
      ),
    );
  }
}
