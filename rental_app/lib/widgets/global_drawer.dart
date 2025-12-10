import 'package:flutter/material.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/utils/constants.dart';

class GlobalDrawer extends StatelessWidget {
  // In a real app, you'd pass a User object or get it from a state manager.
  // For this mock implementation, passing the role is sufficient.
  final UserRole userRole;

  const GlobalDrawer({super.key, required this.userRole});

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Container(
        color: kBackgroundColorLight,
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            _buildHeader(),
            if (userRole == UserRole.admin)
              ..._buildAdminNav(context)
            else
              ..._buildUserNav(context),
            const Divider(color: kTextColorGrey),
            _buildLogout(context),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    // Capitalize role name for display
    final roleName = userRole.name[0].toUpperCase() + userRole.name.substring(1);

    return DrawerHeader(
      decoration: const BoxDecoration(
        gradient: kPrimaryGradient,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.home_work_outlined, color: kTextColorWhite, size: 40),
          const Spacer(),
          const Text(
            'Rental App',
            style: TextStyle(
              color: kTextColorWhite,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          Text(
            'Welcome, $roleName',
            style: TextStyle(
              color: kTextColorWhite.withAlpha((255 * 0.8).round()),
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  List<Widget> _buildAdminNav(BuildContext context) {
    return [
      ListTile(
        leading: const Icon(Icons.dashboard_outlined, color: kTextColorWhite),
        title: const Text('Dashboard', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context); // Close drawer
          // Avoid navigating to the same page if already there
          if (ModalRoute.of(context)?.settings.name != '/admin') {
            Navigator.pushReplacementNamed(context, '/admin');
          }
        },
      ),
      const Divider(color: kTextColorGrey),
      // Quick Actions Section
      Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Text(
          'Quick Actions',
          style: TextStyle(
            color: kTextColorWhite.withAlpha((255 * 0.7).round()),
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      ListTile(
        leading: const Icon(Icons.assignment, color: kPrimaryOrange),
        title: const Text('Applications', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/admin/applications');
        },
      ),
      ListTile(
        leading: const Icon(Icons.approval, color: Colors.indigo),
        title: const Text('Approve Properties', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/admin/property-approvals');
        },
      ),
      ListTile(
        leading: const Icon(Icons.rate_review, color: Colors.amber),
        title: const Text('Reviews', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/admin/reviews');
        },
      ),
      ListTile(
        leading: const Icon(Icons.analytics, color: Colors.green),
        title: const Text('Statistics', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/admin/statistics');
        },
      ),
      const Divider(color: kTextColorGrey),
      ListTile(
        leading: const Icon(Icons.people_outline, color: kTextColorWhite),
        title: const Text('Manage Users', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/admin/manage-users');
        },
      ),
      ListTile(
        leading: const Icon(Icons.business_center, color: kTextColorWhite),
        title: const Text('Landlord Profiles', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/admin/landlord-profiles');
        },
      ),
      ListTile(
        leading: const Icon(Icons.settings, color: kTextColorWhite),
        title: const Text('Settings', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/admin/settings');
        },
      ),
    ];
  }

  List<Widget> _buildUserNav(BuildContext context) {
    // Check if user is a landlord or tenant
    final bool isLandlord = userRole == UserRole.landlord;
    final bool isTenant = userRole == UserRole.tenant;
    
    if (isLandlord) {
      return _buildLandlordNav(context);
    }
    
    if (isTenant) {
      return _buildTenantNav(context);
    }
    
    // Regular guest/user navigation
    return [
      ListTile(
        leading: const Icon(Icons.home_outlined, color: kTextColorWhite),
        title: const Text('Home', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          if (ModalRoute.of(context)?.settings.name != '/home') {
            Navigator.pushReplacementNamed(context, '/home');
          }
        },
      ),
      ListTile(
        leading: const Icon(Icons.person_outline, color: kTextColorWhite),
        title: const Text('Profile', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/profile');
        },
      ),
      ListTile(
        leading: const Icon(Icons.reviews_outlined, color: kTextColorWhite),
        title: const Text('Reviews', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/reviews');
        },
      ),
    ];
  }

  List<Widget> _buildTenantNav(BuildContext context) {
    return [
      ListTile(
        leading: const Icon(Icons.dashboard_outlined, color: kTextColorWhite),
        title: const Text('Dashboard', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          if (ModalRoute.of(context)?.settings.name != '/tenant/dashboard') {
            Navigator.pushReplacementNamed(context, '/tenant/dashboard');
          }
        },
      ),
      const Divider(color: kTextColorGrey),
      // Quick Actions Section
      Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Text(
          'Quick Actions',
          style: TextStyle(
            color: kTextColorWhite.withAlpha((255 * 0.7).round()),
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      ListTile(
        leading: const Icon(Icons.home_work, color: kPrimaryRed),
        title: const Text('My Rentals', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/tenant/rentals');
        },
      ),
      ListTile(
        leading: const Icon(Icons.message, color: Colors.blue),
        title: const Text('Messages', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/tenant/messages');
        },
      ),
      ListTile(
        leading: const Icon(Icons.build, color: kPrimaryOrange),
        title: const Text('Maintenance', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/tenant/maintenance');
        },
      ),
      const Divider(color: kTextColorGrey),
      ListTile(
        leading: const Icon(Icons.history, color: kTextColorWhite),
        title: const Text('Rental History', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/tenant/history');
        },
      ),
      ListTile(
        leading: const Icon(Icons.person_outline, color: kTextColorWhite),
        title: const Text('Profile', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/profile');
        },
      ),
      ListTile(
        leading: const Icon(Icons.settings, color: kTextColorWhite),
        title: const Text('Settings', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/tenant/settings');
        },
      ),
    ];
  }

  List<Widget> _buildLandlordNav(BuildContext context) {
    return [
      ListTile(
        leading: const Icon(Icons.dashboard_outlined, color: kTextColorWhite),
        title: const Text('Dashboard', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          if (ModalRoute.of(context)?.settings.name != '/landlord/dashboard') {
            Navigator.pushReplacementNamed(context, '/landlord/dashboard');
          }
        },
      ),
      const Divider(color: kTextColorGrey),
      // Quick Actions Section
      Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Text(
          'Quick Actions',
          style: TextStyle(
            color: kTextColorWhite.withAlpha((255 * 0.7).round()),
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      ListTile(
        leading: const Icon(Icons.add_home, color: kPrimaryRed),
        title: const Text('Add Property', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/landlord/add-property');
        },
      ),
      ListTile(
        leading: const Icon(Icons.tour, color: kPrimaryOrange),
        title: const Text('Tour Requests', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/landlord/tours');
        },
      ),
      ListTile(
        leading: const Icon(Icons.build, color: Colors.blue),
        title: const Text('Maintenance', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/landlord/maintenance');
        },
      ),
      ListTile(
        leading: const Icon(Icons.message, color: Colors.green),
        title: const Text('Messages', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/landlord/messages');
        },
      ),
      const Divider(color: kTextColorGrey),
      ListTile(
        leading: const Icon(Icons.home_work, color: kTextColorWhite),
        title: const Text('My Properties', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/landlord/properties');
        },
      ),
      ListTile(
        leading: const Icon(Icons.history, color: kTextColorWhite),
        title: const Text('Rental History', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/landlord/rental-history');
        },
      ),
      ListTile(
        leading: const Icon(Icons.person_outline, color: kTextColorWhite),
        title: const Text('Profile', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/profile');
        },
      ),
      ListTile(
        leading: const Icon(Icons.settings, color: kTextColorWhite),
        title: const Text('Settings', style: TextStyle(color: kTextColorWhite)),
        onTap: () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/landlord/settings');
        },
      ),
    ];
  }

  Widget _buildLogout(BuildContext context) {
    return ListTile(
      leading: const Icon(Icons.logout, color: kTextColorWhite),
      title: const Text('Logout', style: TextStyle(color: kTextColorWhite)),
      onTap: () {
        Navigator.pop(context); // Close drawer
        Navigator.pushReplacementNamed(context, '/'); // Navigate to login
      },
    );
  }
}
