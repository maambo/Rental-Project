import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class AdminSettingsScreen extends StatefulWidget {
  const AdminSettingsScreen({super.key});

  @override
  State<AdminSettingsScreen> createState() => _AdminSettingsScreenState();
}

class _AdminSettingsScreenState extends State<AdminSettingsScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Settings'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildSectionHeader('System Settings'),
          _buildSettingsTile(
            icon: Icons.attach_money,
            title: 'Application Fee Configuration',
            subtitle: 'Set fees for each landlord tier',
            color: Colors.green,
            onTap: () {
              // TODO: Navigate to fee configuration
            },
          ),
          _buildSettingsTile(
            icon: Icons.email_outlined,
            title: 'Email Templates',
            subtitle: 'Customize notification emails',
            color: Colors.blue,
            onTap: () {
              // TODO: Navigate to email templates
            },
          ),
          _buildSettingsTile(
            icon: Icons.percent,
            title: 'Platform Commission',
            subtitle: 'Configure commission rates',
            color: Colors.purple,
            onTap: () {
              // TODO: Navigate to commission settings
            },
          ),
          _buildSettingsTile(
            icon: Icons.payment,
            title: 'Payment Gateway',
            subtitle: 'Configure payment methods',
            color: Colors.indigo,
            onTap: () {
              // TODO: Navigate to payment settings
            },
          ),
          const SizedBox(height: 24),
          _buildSectionHeader('User Management'),
          _buildSettingsTile(
            icon: Icons.admin_panel_settings,
            title: 'Roles & Permissions',
            subtitle: 'Manage admin privileges',
            color: kPrimaryRed,
            onTap: () {
              // TODO: Navigate to roles management
            },
          ),
          _buildSettingsTile(
            icon: Icons.verified_user,
            title: 'Account Verification',
            subtitle: 'Configure verification requirements',
            color: Colors.teal,
            onTap: () {
              // TODO: Navigate to verification settings
            },
          ),
          _buildSettingsTile(
            icon: Icons.block,
            title: 'Banned Users',
            subtitle: 'View and manage banned accounts',
            color: kPrimaryRed,
            onTap: () {
              // TODO: Navigate to banned users
            },
          ),
          const SizedBox(height: 24),
          _buildSectionHeader('Property Settings'),
          _buildSettingsTile(
            icon: Icons.category,
            title: 'Property Categories',
            subtitle: 'Manage property types',
            color: kPrimaryOrange,
            onTap: () {
              // TODO: Navigate to categories
            },
          ),
          _buildSettingsTile(
            icon: Icons.checklist,
            title: 'Amenities List',
            subtitle: 'Add/remove available amenities',
            color: Colors.cyan,
            onTap: () {
              // TODO: Navigate to amenities
            },
          ),
          _buildSettingsTile(
            icon: Icons.rule,
            title: 'Approval Criteria',
            subtitle: 'Set property approval guidelines',
            color: Colors.amber,
            onTap: () {
              // TODO: Navigate to approval criteria
            },
          ),
          _buildSettingsTile(
            icon: Icons.star,
            title: 'Featured Properties',
            subtitle: 'Manage promoted listings',
            color: Colors.yellow.shade700,
            onTap: () {
              // TODO: Navigate to featured properties
            },
          ),
          const SizedBox(height: 24),
          _buildSectionHeader('General Settings'),
          _buildSettingsTile(
            icon: Icons.info_outline,
            title: 'Platform Information',
            subtitle: 'Company details and contact info',
            color: Colors.blueGrey,
            onTap: () {
              // TODO: Navigate to platform info
            },
          ),
          _buildSettingsTile(
            icon: Icons.description,
            title: 'Terms & Conditions',
            subtitle: 'Update T&C and privacy policy',
            color: Colors.brown,
            onTap: () {
              // TODO: Navigate to terms
            },
          ),
          _buildSettingsTile(
            icon: Icons.notifications_outlined,
            title: 'Notification Settings',
            subtitle: 'Configure system notifications',
            color: Colors.deepOrange,
            onTap: () {
              // TODO: Navigate to notifications
            },
          ),
          _buildSettingsTile(
            icon: Icons.backup,
            title: 'Backup & Export',
            subtitle: 'Database backup and data export',
            color: Colors.deepPurple,
            onTap: () {
              // TODO: Navigate to backup
            },
          ),
          const SizedBox(height: 24),
          _buildSectionHeader('Analytics & Reports'),
          _buildSettingsTile(
            icon: Icons.schedule,
            title: 'Report Scheduling',
            subtitle: 'Automated report generation',
            color: Colors.green.shade700,
            onTap: () {
              // TODO: Navigate to report scheduling
            },
          ),
          _buildSettingsTile(
            icon: Icons.storage,
            title: 'Data Retention',
            subtitle: 'Configure data storage duration',
            color: Colors.grey.shade700,
            onTap: () {
              // TODO: Navigate to data retention
            },
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12, top: 8),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.bold,
          color: kTextColorBlack,
        ),
      ),
    );
  }

  Widget _buildSettingsTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        onTap: onTap,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: color.withAlpha((255 * 0.1).round()),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: color, size: 24),
        ),
        title: Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: kTextColorBlack,
          ),
        ),
        subtitle: Text(
          subtitle,
          style: TextStyle(
            fontSize: 13,
            color: kTextColorGrey.withAlpha((255 * 0.8).round()),
          ),
        ),
        trailing: Icon(
          Icons.arrow_forward_ios,
          size: 16,
          color: kTextColorGrey.withAlpha((255 * 0.5).round()),
        ),
      ),
    );
  }
}
