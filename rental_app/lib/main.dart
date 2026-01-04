import 'package:flutter/material.dart';
import 'package:rental_app/screens/home_screen.dart';
import 'package:rental_app/screens/login/login_screen.dart';
import 'package:rental_app/screens/profile_screen.dart';
import 'package:rental_app/screens/review_screen.dart';
import 'package:rental_app/screens/admin_dashboard_screen.dart';
import 'package:rental_app/screens/landing_screen.dart';
import 'package:rental_app/screens/admin/landlord_applications_screen.dart';
import 'package:rental_app/screens/admin/landlord_application_detail_screen.dart';
import 'package:rental_app/screens/admin/property_approval_screen.dart';
import 'package:rental_app/screens/admin/reviews_dashboard_screen.dart';
import 'package:rental_app/screens/admin/property_statistics_screen.dart';
import 'package:rental_app/screens/admin/admin_settings_screen.dart';
import 'package:rental_app/screens/admin/landlord_profiles_screen.dart';
import 'package:rental_app/screens/admin/manage_users_screen.dart';
import 'package:rental_app/screens/landlord/landlord_dashboard_screen.dart';
import 'package:rental_app/screens/landlord/landlord_properties_screen.dart';
import 'package:rental_app/screens/landlord/landlord_registration_screen.dart';
import 'package:rental_app/screens/landlord/tour_requests_screen.dart';
import 'package:rental_app/screens/landlord/maintenance_plans_screen.dart';
import 'package:rental_app/screens/landlord/landlord_rental_history_screen.dart';
import 'package:rental_app/screens/tenant/tenant_dashboard_screen.dart';
import 'package:rental_app/screens/tenant/tenant_rentals_screen.dart';
import 'package:rental_app/screens/tenant/tenant_maintenance_screen.dart';
import 'package:rental_app/screens/tenant/tenant_rental_history_screen.dart';
import 'package:rental_app/screens/public/property_detail_screen.dart';
import 'package:rental_app/screens/public/review_submission_screen.dart';
import 'package:rental_app/screens/public/tour_scheduling_screen.dart';
import 'package:rental_app/screens/public/property_application_screen.dart';
import 'package:rental_app/screens/public/public_dashboard_screen.dart';
import 'package:rental_app/screens/chat/chat_list_screen.dart';
import 'package:rental_app/screens/chat/chat_detail_screen.dart';
import 'package:rental_app/screens/notifications_screen.dart';
import 'package:rental_app/screens/admin/revenue_report_screen.dart';
import 'package:rental_app/models/property_model.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/utils/constants.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Rental App',
      theme: ThemeData(
        primaryColor: kPrimaryRed,
        scaffoldBackgroundColor: kBackgroundColorLight,
        colorScheme: ColorScheme.fromSwatch(
          brightness: Brightness.dark,
          primarySwatch: MaterialColor(0xFFE21608, {
            50: kPrimaryRed.withAlpha((255 * 0.1).round()),
            100: kPrimaryRed.withAlpha((255 * 0.2).round()),
            200: kPrimaryRed.withAlpha((255 * 0.3).round()),
            300: kPrimaryRed.withAlpha((255 * 0.4).round()),
            400: kPrimaryRed.withAlpha((255 * 0.5).round()),
            500: kPrimaryRed.withAlpha((255 * 0.6).round()),
            600: kPrimaryRed.withAlpha((255 * 0.7).round()),
            700: kPrimaryRed.withAlpha((255 * 0.8).round()),
            800: kPrimaryRed.withAlpha((255 * 0.9).round()),
            900: kPrimaryRed.withAlpha((255 * 1.0).round()),
          }),
        ).copyWith(secondary: kPrimaryOrange, surface: kBackgroundColorDark),
        visualDensity: VisualDensity.adaptivePlatformDensity,
        appBarTheme: const AppBarTheme(
          backgroundColor: kBackgroundColorLight,
          elevation: 0,
          titleTextStyle: TextStyle(color: kTextColorWhite, fontSize: 20, fontWeight: FontWeight.bold),
          iconTheme: IconThemeData(color: kTextColorWhite),
        ),
        textButtonTheme: TextButtonThemeData(
          style: TextButton.styleFrom(
            foregroundColor: kPrimaryOrange,
          ),
        ),
      ),
      initialRoute: '/public',
      routes: {
        '/public': (context) => const PublicDashboardScreen(),
        '/landing': (context) => const LandingScreen(),
        '/': (context) => const LoginScreen(),
        '/home': (context) => const HomeScreen(),
        '/profile': (context) => const ProfileScreen(),
        '/reviews': (context) => const ReviewScreen(),
        '/admin': (context) => const AdminDashboardScreen(),
        '/admin/applications': (context) => const LandlordApplicationsScreen(),
        '/admin/property-approvals': (context) => const PropertyApprovalScreen(),
        '/admin/reviews': (context) => const ReviewsDashboardScreen(),
        '/admin/statistics': (context) => const PropertyStatisticsScreen(),
        '/admin/settings': (context) => const AdminSettingsScreen(),
        '/admin/landlord-profiles': (context) => const LandlordProfilesScreen(),

        // '/admin/manage-users' handled in onGenerateRoute
        '/landlord/register': (context) => const LandlordRegistrationScreen(),
        '/landlord/dashboard': (context) => const LandlordDashboardScreen(),
        '/landlord/properties': (context) => const LandlordPropertiesScreen(),
        '/landlord/tours': (context) => const TourRequestsScreen(),
        '/landlord/maintenance': (context) => const MaintenancePlansScreen(),
        '/landlord/rental-history': (context) => const LandlordRentalHistoryScreen(),
        '/tenant/dashboard': (context) => const TenantDashboardScreen(),
        '/tenant/rentals': (context) => const TenantRentalsScreen(),
        '/tenant/maintenance': (context) => const TenantMaintenanceScreen(),
        '/tenant/history': (context) => const TenantRentalHistoryScreen(),
        '/chat': (context) => const ChatListScreen(),
        '/notifications': (context) => const NotificationsScreen(),
        '/admin/revenue': (context) => const RevenueReportScreen(),
      },
      onGenerateRoute: (settings) {
        // Handle property detail routes with arguments
        if (settings.name != null && settings.name!.startsWith('/property/')) {
          final property = settings.arguments as Property?;
          if (property == null) return null;
          
          if (settings.name!.endsWith('/review')) {
            return MaterialPageRoute(
              builder: (context) => ReviewSubmissionScreen(property: property),
            );
          } else if (settings.name!.endsWith('/tour')) {
            return MaterialPageRoute(
              builder: (context) => TourSchedulingScreen(property: property),
            );
          } else if (settings.name!.endsWith('/apply')) {
            return MaterialPageRoute(
              builder: (context) => PropertyApplicationScreen(property: property),
            );
          } else {
            return MaterialPageRoute(
              builder: (context) => PropertyDetailScreen(property: property),
            );
          }
        }

        
        if (settings.name == '/chat/detail') {
          final args = settings.arguments as Map<String, dynamic>?;
          return MaterialPageRoute(
            builder: (context) => ChatDetailScreen(
              userName: args?['name'] ?? 'Chat',
            ),
          );
        }

        if (settings.name == '/admin/manage-users') {
          final args = settings.arguments as Map<String, dynamic>?;
          return MaterialPageRoute(
            builder: (context) => ManageUsersScreen(
              initialFilterRole: args?['role'] as UserRole?,
            ),
          );
        }

        return null;
      },
    );
  }
}