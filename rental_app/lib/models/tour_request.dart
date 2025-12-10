enum TourStatus {
  pending,
  approved,
  declined,
  completed,
}

extension TourStatusExtension on TourStatus {
  String get displayName {
    switch (this) {
      case TourStatus.pending:
        return 'Pending';
      case TourStatus.approved:
        return 'Approved';
      case TourStatus.declined:
        return 'Declined';
      case TourStatus.completed:
        return 'Completed';
    }
  }

  static TourStatus fromString(String value) {
    return TourStatus.values.firstWhere(
      (status) => status.toString().split('.').last == value,
      orElse: () => TourStatus.pending,
    );
  }
}

class TourRequest {
  final String id;
  final String propertyId;
  final String propertyTitle;
  final String userName;
  final String email;
  final String phone;
  final DateTime preferredDate;
  final String? additionalNotes;
  final TourStatus status;
  final String? landlordFeedback;
  final DateTime createdDate;

  TourRequest({
    required this.id,
    required this.propertyId,
    required this.propertyTitle,
    required this.userName,
    required this.email,
    required this.phone,
    required this.preferredDate,
    this.additionalNotes,
    required this.status,
    this.landlordFeedback,
    required this.createdDate,
  });

  factory TourRequest.fromJson(Map<String, dynamic> json) {
    return TourRequest(
      id: json['id'] as String,
      propertyId: json['propertyId'] as String,
      propertyTitle: json['propertyTitle'] as String,
      userName: json['userName'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String,
      preferredDate: DateTime.parse(json['preferredDate'] as String),
      additionalNotes: json['additionalNotes'] as String?,
      status: TourStatusExtension.fromString(json['status'] as String),
      landlordFeedback: json['landlordFeedback'] as String?,
      createdDate: DateTime.parse(json['createdDate'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'propertyId': propertyId,
      'propertyTitle': propertyTitle,
      'userName': userName,
      'email': email,
      'phone': phone,
      'preferredDate': preferredDate.toIso8601String(),
      'additionalNotes': additionalNotes,
      'status': status.toString().split('.').last,
      'landlordFeedback': landlordFeedback,
      'createdDate': createdDate.toIso8601String(),
    };
  }

  TourRequest copyWith({
    String? id,
    String? propertyId,
    String? propertyTitle,
    String? userName,
    String? email,
    String? phone,
    DateTime? preferredDate,
    String? additionalNotes,
    TourStatus? status,
    String? landlordFeedback,
    DateTime? createdDate,
  }) {
    return TourRequest(
      id: id ?? this.id,
      propertyId: propertyId ?? this.propertyId,
      propertyTitle: propertyTitle ?? this.propertyTitle,
      userName: userName ?? this.userName,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      preferredDate: preferredDate ?? this.preferredDate,
      additionalNotes: additionalNotes ?? this.additionalNotes,
      status: status ?? this.status,
      landlordFeedback: landlordFeedback ?? this.landlordFeedback,
      createdDate: createdDate ?? this.createdDate,
    );
  }
}
