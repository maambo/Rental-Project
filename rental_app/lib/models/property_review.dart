class PropertyReview {
  final String id;
  final String propertyId;
  final String userId;
  final String userName;
  final double rating; // 1.0 to 5.0
  final String comment;
  final DateTime createdDate;
  final DateTime? updatedDate;

  PropertyReview({
    required this.id,
    required this.propertyId,
    required this.userId,
    required this.userName,
    required this.rating,
    required this.comment,
    required this.createdDate,
    this.updatedDate,
  });

  factory PropertyReview.fromJson(Map<String, dynamic> json) {
    return PropertyReview(
      id: json['id'] as String,
      propertyId: json['propertyId'] as String,
      userId: json['userId'] as String,
      userName: json['userName'] as String,
      rating: (json['rating'] as num).toDouble(),
      comment: json['comment'] as String,
      createdDate: DateTime.parse(json['createdDate'] as String),
      updatedDate: json['updatedDate'] != null
          ? DateTime.parse(json['updatedDate'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'propertyId': propertyId,
      'userId': userId,
      'userName': userName,
      'rating': rating,
      'comment': comment,
      'createdDate': createdDate.toIso8601String(),
      'updatedDate': updatedDate?.toIso8601String(),
    };
  }

  PropertyReview copyWith({
    String? id,
    String? propertyId,
    String? userId,
    String? userName,
    double? rating,
    String? comment,
    DateTime? createdDate,
    DateTime? updatedDate,
  }) {
    return PropertyReview(
      id: id ?? this.id,
      propertyId: propertyId ?? this.propertyId,
      userId: userId ?? this.userId,
      userName: userName ?? this.userName,
      rating: rating ?? this.rating,
      comment: comment ?? this.comment,
      createdDate: createdDate ?? this.createdDate,
      updatedDate: updatedDate ?? this.updatedDate,
    );
  }
}
