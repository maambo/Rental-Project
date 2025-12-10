enum MaintenanceStatus {
  scheduled,
  inProgress,
  completed,
  cancelled,
}

extension MaintenanceStatusExtension on MaintenanceStatus {
  String get displayName {
    switch (this) {
      case MaintenanceStatus.scheduled:
        return 'Scheduled';
      case MaintenanceStatus.inProgress:
        return 'In Progress';
      case MaintenanceStatus.completed:
        return 'Completed';
      case MaintenanceStatus.cancelled:
        return 'Cancelled';
    }
  }

  static MaintenanceStatus fromString(String value) {
    return MaintenanceStatus.values.firstWhere(
      (status) => status.toString().split('.').last == value,
      orElse: () => MaintenanceStatus.scheduled,
    );
  }
}

class MaintenancePlan {
  final String id;
  final String propertyId;
  final String propertyTitle;
  final String landlordId;
  final String title;
  final String description;
  final DateTime scheduledDate;
  final MaintenanceStatus status;
  final double? cost;
  final DateTime createdDate;
  final DateTime? completedDate;

  MaintenancePlan({
    required this.id,
    required this.propertyId,
    required this.propertyTitle,
    required this.landlordId,
    required this.title,
    required this.description,
    required this.scheduledDate,
    required this.status,
    this.cost,
    required this.createdDate,
    this.completedDate,
  });

  factory MaintenancePlan.fromJson(Map<String, dynamic> json) {
    return MaintenancePlan(
      id: json['id'] as String,
      propertyId: json['propertyId'] as String,
      propertyTitle: json['propertyTitle'] as String,
      landlordId: json['landlordId'] as String,
      title: json['title'] as String,
      description: json['description'] as String,
      scheduledDate: DateTime.parse(json['scheduledDate'] as String),
      status: MaintenanceStatusExtension.fromString(json['status'] as String),
      cost: json['cost'] != null ? (json['cost'] as num).toDouble() : null,
      createdDate: DateTime.parse(json['createdDate'] as String),
      completedDate: json['completedDate'] != null
          ? DateTime.parse(json['completedDate'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'propertyId': propertyId,
      'propertyTitle': propertyTitle,
      'landlordId': landlordId,
      'title': title,
      'description': description,
      'scheduledDate': scheduledDate.toIso8601String(),
      'status': status.toString().split('.').last,
      'cost': cost,
      'createdDate': createdDate.toIso8601String(),
      'completedDate': completedDate?.toIso8601String(),
    };
  }

  MaintenancePlan copyWith({
    String? id,
    String? propertyId,
    String? propertyTitle,
    String? landlordId,
    String? title,
    String? description,
    DateTime? scheduledDate,
    MaintenanceStatus? status,
    double? cost,
    DateTime? createdDate,
    DateTime? completedDate,
  }) {
    return MaintenancePlan(
      id: id ?? this.id,
      propertyId: propertyId ?? this.propertyId,
      propertyTitle: propertyTitle ?? this.propertyTitle,
      landlordId: landlordId ?? this.landlordId,
      title: title ?? this.title,
      description: description ?? this.description,
      scheduledDate: scheduledDate ?? this.scheduledDate,
      status: status ?? this.status,
      cost: cost ?? this.cost,
      createdDate: createdDate ?? this.createdDate,
      completedDate: completedDate ?? this.completedDate,
    );
  }
}
