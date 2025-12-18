import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';
import 'package:rental_app/widgets/dashboard_stat_card.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/widgets/global_drawer.dart';

class TenantDashboardScreen extends StatefulWidget {
  const TenantDashboardScreen({super.key});

  @override
  State<TenantDashboardScreen> createState() => _TenantDashboardScreenState();
}

class _TenantDashboardScreenState extends State<TenantDashboardScreen> {
  // TODO: Get from actual user data
  final String _tenantName = 'John Doe';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {
              Navigator.pushNamed(context, '/notifications');
            },
          ),
        ],
      ),
      drawer: const GlobalDrawer(userRole: UserRole.tenant),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildWelcomeCard(),
            const SizedBox(height: 24),
            _buildStatsSection(),
            const SizedBox(height: 24),
            _buildQuickActions(),
            const SizedBox(height: 24),
            _buildUpcomingMaintenance(),
          ],
        ),
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
            child: const Icon(Icons.home, color: Colors.white, size: 32),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Welcome back, $_tenantName!',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Manage your rentals and stay updated',
                  style: TextStyle(
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
          title: 'Current Rentals',
          value: '2',
          icon: Icons.home_work,
          color: kPrimaryRed,
          subtitle: 'Active properties',
          onTap: () {
            Navigator.pushNamed(context, '/tenant/rentals');
          },
        ),
        const SizedBox(height: 16),
        DashboardStatCard(
          title: 'Unread Messages',
          value: '5',
          icon: Icons.message_outlined,
          color: Colors.blue,
          subtitle: 'From landlords',
          trend: '2 new',
          trendUp: false,
          onTap: () {
            Navigator.pushNamed(context, '/chat');
          },
        ),
        const SizedBox(height: 16),
        DashboardStatCard(
          title: 'Upcoming Maintenance',
          value: '3',
          icon: Icons.build_outlined,
          color: kPrimaryOrange,
          subtitle: 'Scheduled this month',
          onTap: () {
            Navigator.pushNamed(context, '/tenant/maintenance');
          },
        ),
        const SizedBox(height: 16),
        DashboardStatCard(
          title: 'Rent Due',
          value: 'K3.5k',
          icon: Icons.payment,
          color: Colors.green,
          subtitle: 'Due in 5 days',
          trend: 'On time',
          trendUp: true,
          onTap: () {
            Navigator.pushNamed(context, '/tenant/rentals');
          },
        ),
      ],
    );
  }

  Widget _buildQuickActions() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Quick Actions',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: kTextColorBlack,
          ),
        ),
        const SizedBox(height: 16),
        Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            _buildActionButton(
              'My Rentals',
              Icons.home_work,
              kPrimaryRed,
              () {
                Navigator.pushNamed(context, '/tenant/rentals');
              },
            ),
            _buildActionButton(
              'Messages',
              Icons.message,
              Colors.blue,
              () {
                Navigator.pushNamed(context, '/chat');
              },
            ),
            _buildActionButton(
              'Maintenance',
              Icons.build,
              kPrimaryOrange,
              () {
                Navigator.pushNamed(context, '/tenant/maintenance');
              },
            ),
            _buildActionButton(
              'History',
              Icons.history,
              Colors.green,
              () {
                Navigator.pushNamed(context, '/tenant/history');
              },
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionButton(
    String label,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        width: (MediaQuery.of(context).size.width - 44) / 2,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: color.withAlpha((255 * 0.1).round()),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withAlpha((255 * 0.3).round())),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.w600,
                fontSize: 14,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildUpcomingMaintenance() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Upcoming Maintenance',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: kTextColorBlack,
          ),
        ),
        const SizedBox(height: 16),
        _buildMaintenanceItem(
          'Plumbing Inspection',
          'Modern Downtown Apartment',
          'Dec 15, 2024',
          Icons.plumbing,
          Colors.blue,
        ),
        _buildMaintenanceItem(
          'HVAC Servicing',
          'Modern Downtown Apartment',
          'Dec 20, 2024',
          Icons.ac_unit,
          kPrimaryOrange,
        ),
        _buildMaintenanceItem(
          'Electrical Check',
          'Cozy Garden House',
          'Dec 25, 2024',
          Icons.electrical_services,
          Colors.amber,
        ),
      ],
    );
  }

  Widget _buildMaintenanceItem(
    String title,
    String property,
    String date,
    IconData icon,
    Color color,
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
                  property,
                  style: TextStyle(
                    fontSize: 12,
                    color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                  ),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Icon(
                Icons.calendar_today,
                size: 14,
                color: kTextColorGrey.withAlpha((255 * 0.6).round()),
              ),
              const SizedBox(height: 4),
              Text(
                date,
                style: TextStyle(
                  fontSize: 12,
                  color: kTextColorGrey.withAlpha((255 * 0.6).round()),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
