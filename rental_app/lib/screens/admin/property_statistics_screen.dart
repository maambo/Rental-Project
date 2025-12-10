import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class PropertyStatisticsScreen extends StatefulWidget {
  const PropertyStatisticsScreen({super.key});

  @override
  State<PropertyStatisticsScreen> createState() => _PropertyStatisticsScreenState();
}

class _PropertyStatisticsScreenState extends State<PropertyStatisticsScreen> {
  String _selectedTab = 'top_rated'; // top_rated, most_viewed, most_tours

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Property Statistics'),
      ),
      body: Column(
        children: [
          _buildTabBar(),
          Expanded(child: _buildContent()),
        ],
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
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
          Expanded(
            child: _buildTabButton(
              'Top Rated',
              'top_rated',
              Icons.star,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _buildTabButton(
              'Most Viewed',
              'most_viewed',
              Icons.visibility,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _buildTabButton(
              'Most Tours',
              'most_tours',
              Icons.tour,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTabButton(String label, String value, IconData icon) {
    final isSelected = _selectedTab == value;
    return InkWell(
      onTap: () {
        setState(() {
          _selectedTab = value;
        });
      },
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: isSelected
              ? kPrimaryRed
              : kTextColorGrey.withAlpha((255 * 0.1).round()),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: isSelected ? Colors.white : kTextColorGrey,
              size: 20,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: isSelected ? Colors.white : kTextColorGrey,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    // TODO: Replace with actual data
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildOverviewCards(),
        const SizedBox(height: 24),
        _buildPropertyList(),
      ],
    );
  }

  Widget _buildOverviewCards() {
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
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Total Properties',
                '156',
                Icons.home_work,
                kPrimaryRed,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'Avg Rating',
                '4.6',
                Icons.star,
                Colors.amber,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Total Views',
                '12.5K',
                Icons.visibility,
                Colors.blue,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'Tour Requests',
                '342',
                Icons.tour,
                Colors.green,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Container(
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withAlpha((255 * 0.1).round()),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: kTextColorBlack,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: kTextColorGrey.withAlpha((255 * 0.7).round()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPropertyList() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          _getListTitle(),
          style: const TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: kTextColorBlack,
          ),
        ),
        const SizedBox(height: 16),
        // TODO: Replace with actual data
        _buildEmptyState(),
      ],
    );
  }

  String _getListTitle() {
    switch (_selectedTab) {
      case 'top_rated':
        return 'Top Rated Properties';
      case 'most_viewed':
        return 'Most Viewed Properties';
      case 'most_tours':
        return 'Properties with Most Tour Requests';
      default:
        return 'Properties';
    }
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          children: [
            Icon(
              Icons.analytics_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              'No data available yet',
              style: TextStyle(
                fontSize: 16,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
