import 'package:flutter/material.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/services/data_service.dart';
import 'package:rental_app/utils/constants.dart';
import 'package:rental_app/widgets/dashboard_stat_card.dart';
import 'package:rental_app/widgets/global_drawer.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  late Future<Map<String, dynamic>> _statsFuture;

  @override
  void initState() {
    super.initState();
    _statsFuture = DataService().getDashboardStats();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Admin Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              setState(() {
                _statsFuture = DataService().getDashboardStats();
              });
            },
          ),
        ],
      ),
      drawer: GlobalDrawer(userRole: UserRole.admin),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _statsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 64, color: kPrimaryRed),
                  const SizedBox(height: 16),
                  Text('Error: ${snapshot.error}'),
                ],
              ),
            );
          } else if (!snapshot.hasData) {
            return const Center(child: Text('No data available'));
          }

          final stats = snapshot.data!;
          return _buildDashboard(stats);
        },
      ),
    );
  }

  Widget _buildDashboard(Map<String, dynamic> stats) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Welcome Section
          Container(
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
                    color: kTextColorWhite.withAlpha((255 * 0.2).round()),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.dashboard, color: kTextColorWhite, size: 32),
                ),
                const SizedBox(width: 16),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Welcome, Admin!',
                        style: TextStyle(
                          color: kTextColorWhite,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Here\'s an overview of your rental platform',
                        style: TextStyle(
                          color: kTextColorWhite,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Stats List
          Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              DashboardStatCard(
                title: 'Total Properties',
                value: '${stats['totalProperties']}',
                icon: Icons.home_work,
                color: kPrimaryRed,
                subtitle: '${stats['vacantProperties']} vacant',
                trend: '+12% vs last month',
                trendUp: true,
                onTap: () {
                  // TODO: Navigate to properties list
                },
              ),
              const SizedBox(height: 16),
              DashboardStatCard(
                title: 'Landlords',
                value: '${stats['totalLandlords']}',
                icon: Icons.person_outline,
                color: Colors.blue,
                trend: '+5% new this week',
                trendUp: true,
                onTap: () {
                  Navigator.pushNamed(
                    context,
                    '/admin/manage-users',
                    arguments: {'role': UserRole.landlord},
                  );
                },
              ),
              const SizedBox(height: 16),
              DashboardStatCard(
                title: 'Tenants',
                value: '${stats['totalTenants']}',
                icon: Icons.people_outline,
                color: Colors.green,
                trend: '+8% active users',
                trendUp: true,
                onTap: () {
                  Navigator.pushNamed(
                    context,
                    '/admin/manage-users',
                    arguments: {'role': UserRole.tenant},
                  );
                },
              ),
              const SizedBox(height: 16),
              DashboardStatCard(
                title: 'Occupied',
                value: '${stats['occupiedProperties']}',
                icon: Icons.check_circle_outline,
                color: kPrimaryOrange,
                subtitle: '${stats['occupancyRate'].toStringAsFixed(1)}% rate',
                trend: '-2% vs last month',
                trendUp: false,
                onTap: () {
                  // TODO: Navigate to occupied properties
                },
              ),
              const SizedBox(height: 16),
              DashboardStatCard(
                title: 'Monthly Revenue',
                value: 'K${(stats['totalRevenue'] / 1000).toStringAsFixed(1)}k',
                icon: Icons.attach_money,
                color: Colors.purple,
                trend: '+15% revenue growth',
                trendUp: true,
                onTap: () {
                  Navigator.pushNamed(context, '/admin/revenue');
                },
              ),
              const SizedBox(height: 16),
              DashboardStatCard(
                title: 'Avg Rating',
                value: stats['averageRating'].toStringAsFixed(1),
                icon: Icons.star_outline,
                color: Colors.amber,
                subtitle: '${stats['totalReviews']} reviews',
                trend: '+0.2 points',
                trendUp: true,
                onTap: () {
                  // TODO: Navigate to reviews
                },
              ),
            ],
          ),
        ],
      ),
    );
  }
}