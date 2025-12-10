import 'package:rental_app/models/landlord_tier.dart';
import 'package:rental_app/models/application_status.dart';

enum UserRole {
  admin,
  landlord,
  tenant,
}

class User {
  final String id;
  final String name;
  final String email;
  final UserRole role;
  
  // Landlord-specific fields
  final LandlordTier? landlordTier;
  final ApplicationStatus? applicationStatus;
  final DateTime? approvedDate;
  final int propertyCount;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.landlordTier,
    this.applicationStatus,
    this.approvedDate,
    this.propertyCount = 0,
  });

  // Getter to check if landlord is approved
  bool get isApproved {
    if (role != UserRole.landlord) return true;
    return applicationStatus == ApplicationStatus.approved;
  }

  // Factory constructor to create a User from a JSON map
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as String,
      name: json['name'] as String,
      email: json['email'] as String,
      role: UserRole.values.firstWhere(
        (e) => e.toString().split('.').last == json['role'],
        orElse: () => UserRole.tenant, // Default role if not found
      ),
      landlordTier: json['landlordTier'] != null
          ? LandlordTierExtension.fromString(json['landlordTier'] as String)
          : null,
      applicationStatus: json['applicationStatus'] != null
          ? ApplicationStatusExtension.fromString(json['applicationStatus'] as String)
          : null,
      approvedDate: json['approvedDate'] != null
          ? DateTime.parse(json['approvedDate'] as String)
          : null,
      propertyCount: json['propertyCount'] as int? ?? 0,
    );
  }

  // Method to convert a User object to a JSON map
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role.toString().split('.').last,
      'landlordTier': landlordTier?.toString().split('.').last,
      'applicationStatus': applicationStatus?.toString().split('.').last,
      'approvedDate': approvedDate?.toIso8601String(),
      'propertyCount': propertyCount,
    };
  }
}