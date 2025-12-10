import 'package:rental_app/models/landlord_tier.dart';
import 'package:rental_app/models/application_status.dart';

class LandlordApplication {
  final String id;
  final String userId;
  final String userName;
  final String email;
  final LandlordTier tier;
  final ApplicationStatus status;
  final List<String> documents; // URLs or paths to uploaded documents
  final double applicationFee;
  final DateTime submittedDate;
  final DateTime? reviewedDate;
  final String? adminNotes;
  final String? rejectionReason;

  LandlordApplication({
    required this.id,
    required this.userId,
    required this.userName,
    required this.email,
    required this.tier,
    required this.status,
    required this.documents,
    required this.applicationFee,
    required this.submittedDate,
    this.reviewedDate,
    this.adminNotes,
    this.rejectionReason,
  });

  factory LandlordApplication.fromJson(Map<String, dynamic> json) {
    return LandlordApplication(
      id: json['id'] as String,
      userId: json['userId'] as String,
      userName: json['userName'] as String,
      email: json['email'] as String,
      tier: LandlordTierExtension.fromString(json['tier'] as String),
      status: ApplicationStatusExtension.fromString(json['status'] as String),
      documents: List<String>.from(json['documents'] as List<dynamic>),
      applicationFee: (json['applicationFee'] as num).toDouble(),
      submittedDate: DateTime.parse(json['submittedDate'] as String),
      reviewedDate: json['reviewedDate'] != null
          ? DateTime.parse(json['reviewedDate'] as String)
          : null,
      adminNotes: json['adminNotes'] as String?,
      rejectionReason: json['rejectionReason'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'userId': userId,
      'userName': userName,
      'email': email,
      'tier': tier.toString().split('.').last,
      'status': status.toString().split('.').last,
      'documents': documents,
      'applicationFee': applicationFee,
      'submittedDate': submittedDate.toIso8601String(),
      'reviewedDate': reviewedDate?.toIso8601String(),
      'adminNotes': adminNotes,
      'rejectionReason': rejectionReason,
    };
  }

  LandlordApplication copyWith({
    String? id,
    String? userId,
    String? userName,
    String? email,
    LandlordTier? tier,
    ApplicationStatus? status,
    List<String>? documents,
    double? applicationFee,
    DateTime? submittedDate,
    DateTime? reviewedDate,
    String? adminNotes,
    String? rejectionReason,
  }) {
    return LandlordApplication(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      userName: userName ?? this.userName,
      email: email ?? this.email,
      tier: tier ?? this.tier,
      status: status ?? this.status,
      documents: documents ?? this.documents,
      applicationFee: applicationFee ?? this.applicationFee,
      submittedDate: submittedDate ?? this.submittedDate,
      reviewedDate: reviewedDate ?? this.reviewedDate,
      adminNotes: adminNotes ?? this.adminNotes,
      rejectionReason: rejectionReason ?? this.rejectionReason,
    );
  }
}
