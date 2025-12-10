enum RentalStatus {
  active,
  completed,
  terminated,
}

extension RentalStatusExtension on RentalStatus {
  String get displayName {
    switch (this) {
      case RentalStatus.active:
        return 'Active';
      case RentalStatus.completed:
        return 'Completed';
      case RentalStatus.terminated:
        return 'Terminated';
    }
  }

  static RentalStatus fromString(String value) {
    return RentalStatus.values.firstWhere(
      (status) => status.toString().split('.').last == value,
      orElse: () => RentalStatus.active,
    );
  }
}

class RentalHistory {
  final String id;
  final String propertyId;
  final String propertyTitle;
  final String tenantId;
  final String tenantName;
  final String landlordId;
  final String landlordName;
  final DateTime startDate;
  final DateTime? endDate;
  final double monthlyRent;
  final RentalStatus status;

  RentalHistory({
    required this.id,
    required this.propertyId,
    required this.propertyTitle,
    required this.tenantId,
    required this.tenantName,
    required this.landlordId,
    required this.landlordName,
    required this.startDate,
    this.endDate,
    required this.monthlyRent,
    required this.status,
  });

  factory RentalHistory.fromJson(Map<String, dynamic> json) {
    return RentalHistory(
      id: json['id'] as String,
      propertyId: json['propertyId'] as String,
      propertyTitle: json['propertyTitle'] as String,
      tenantId: json['tenantId'] as String,
      tenantName: json['tenantName'] as String,
      landlordId: json['landlordId'] as String,
      landlordName: json['landlordName'] as String,
      startDate: DateTime.parse(json['startDate'] as String),
      endDate: json['endDate'] != null
          ? DateTime.parse(json['endDate'] as String)
          : null,
      monthlyRent: (json['monthlyRent'] as num).toDouble(),
      status: RentalStatusExtension.fromString(json['status'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'propertyId': propertyId,
      'propertyTitle': propertyTitle,
      'tenantId': tenantId,
      'tenantName': tenantName,
      'landlordId': landlordId,
      'landlordName': landlordName,
      'startDate': startDate.toIso8601String(),
      'endDate': endDate?.toIso8601String(),
      'monthlyRent': monthlyRent,
      'status': status.toString().split('.').last,
    };
  }

  RentalHistory copyWith({
    String? id,
    String? propertyId,
    String? propertyTitle,
    String? tenantId,
    String? tenantName,
    String? landlordId,
    String? landlordName,
    DateTime? startDate,
    DateTime? endDate,
    double? monthlyRent,
    RentalStatus? status,
  }) {
    return RentalHistory(
      id: id ?? this.id,
      propertyId: propertyId ?? this.propertyId,
      propertyTitle: propertyTitle ?? this.propertyTitle,
      tenantId: tenantId ?? this.tenantId,
      tenantName: tenantName ?? this.tenantName,
      landlordId: landlordId ?? this.landlordId,
      landlordName: landlordName ?? this.landlordName,
      startDate: startDate ?? this.startDate,
      endDate: endDate ?? this.endDate,
      monthlyRent: monthlyRent ?? this.monthlyRent,
      status: status ?? this.status,
    );
  }
}
