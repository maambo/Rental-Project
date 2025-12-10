import 'dart:convert';
import 'package:flutter/services.dart' show rootBundle;
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/models/property_model.dart';

class DataService {
  static const String _dataPath = 'assets/db.json';

  Future<List<Property>> loadProperties() async {
    final String jsonString = await rootBundle.loadString(_dataPath);
    final Map<String, dynamic> jsonMap = json.decode(jsonString);

    final List<dynamic> propertyJsonList = jsonMap['properties'] as List<dynamic>;
    return propertyJsonList.map((json) => Property.fromJson(json)).toList();
  }

  Future<List<User>> loadUsers() async {
    final String jsonString = await rootBundle.loadString(_dataPath);
    final Map<String, dynamic> jsonMap = json.decode(jsonString);

    final List<dynamic> userJsonList = jsonMap['users'] as List<dynamic>;
    return userJsonList.map((json) => User.fromJson(json)).toList();
  }

  // You can add similar methods for reviews if needed later
  Future<List<Map<String, dynamic>>> loadReviews() async {
    final String jsonString = await rootBundle.loadString(_dataPath);
    final Map<String, dynamic> jsonMap = json.decode(jsonString);

    final List<dynamic> reviewJsonList = jsonMap['reviews'] as List<dynamic>;
    return reviewJsonList.cast<Map<String, dynamic>>();
  }

  Future<Map<String, dynamic>> getDashboardStats() async {
    final properties = await loadProperties();
    final users = await loadUsers();
    final reviews = await loadReviews();

    // Count landlords and tenants
    final landlords = users.where((user) => user.role == UserRole.landlord).length;
    final tenants = users.where((user) => user.role == UserRole.tenant).length;

    // Count occupied properties
    final occupiedProperties = properties.where((prop) => prop.occupied).length;

    // Calculate total monthly revenue
    final totalRevenue = properties.fold<double>(
      0,
      (sum, prop) => sum + (prop.occupied ? prop.pricePerMonth : 0),
    );

    // Calculate average rating
    final avgRating = properties.isEmpty
        ? 0.0
        : properties.fold<double>(0, (sum, prop) => sum + prop.rating) / properties.length;

    return {
      'totalProperties': properties.length,
      'totalLandlords': landlords,
      'totalTenants': tenants,
      'occupiedProperties': occupiedProperties,
      'vacantProperties': properties.length - occupiedProperties,
      'totalRevenue': totalRevenue,
      'averageRating': avgRating,
      'totalReviews': reviews.length,
      'occupancyRate': properties.isEmpty ? 0.0 : (occupiedProperties / properties.length) * 100,
    };
  }
}
