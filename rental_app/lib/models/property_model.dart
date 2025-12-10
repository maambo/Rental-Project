import 'package:rental_app/models/application_status.dart';

class Property {
  final String id;
  final String landlordId; // Assuming landlordId links to a User
  final String title;
  final String description;
  final List<String> imageUrls; // Changed to support multiple images
  final double pricePerMonth;
  final String location;
  final int beds;
  final int baths;
  final int sqft;
  final double rating;
  final int reviews;
  final String type;
  final List<String> amenities;
  final String landlordName;
  final String landlordPhone;
  final String landlordEmail;
  final bool occupied;
  final double latitude;
  final double longitude;
  
  // New fields for approval workflow
  final bool isApproved;
  final ApplicationStatus approvalStatus;
  final DateTime submittedDate;
  final DateTime? approvedDate;
  
  // New fields for statistics
  final int viewCount;
  final int likeCount;

  Property({
    required this.id,
    required this.landlordId,
    required this.title,
    required this.description,
    required this.imageUrls,
    required this.pricePerMonth,
    required this.location,
    required this.beds,
    required this.baths,
    required this.sqft,
    required this.rating,
    required this.reviews,
    required this.type,
    required this.amenities,
    required this.landlordName,
    required this.landlordPhone,
    required this.landlordEmail,
    this.occupied = false,
    this.latitude = 0.0,
    this.longitude = 0.0,
    this.isApproved = false,
    this.approvalStatus = ApplicationStatus.pending,
    required this.submittedDate,
    this.approvedDate,
    this.viewCount = 0,
    this.likeCount = 0,
  });

  factory Property.fromJson(Map<String, dynamic> json) {
    // Handle both single image (legacy) and multiple images
    List<String> images = [];
    if (json['imageUrls'] != null) {
      images = List<String>.from(json['imageUrls'] as List<dynamic>);
    } else if (json['image'] != null) {
      images = [json['image'] as String];
    }
    
    return Property(
      id: json['id'] as String,
      landlordId: json['landlordId'] as String,
      title: json['title'] as String,
      description: json['description'] as String,
      imageUrls: images,
      pricePerMonth: (json['pricePerMonth'] as num).toDouble(),
      location: json['location'] as String,
      beds: json['beds'] as int,
      baths: json['baths'] as int,
      sqft: json['sqft'] as int,
      rating: (json['rating'] as num).toDouble(),
      reviews: json['reviews'] as int,
      type: json['type'] as String,
      amenities: List<String>.from(json['amenities'] as List<dynamic>),
      landlordName: json['landlord'] as String,
      landlordPhone: json['landlordPhone'] as String,
      landlordEmail: json['landlordEmail'] as String,
      occupied: json['occupied'] as bool? ?? false,
      latitude: (json['latitude'] as num?)?.toDouble() ?? -15.3875,
      longitude: (json['longitude'] as num?)?.toDouble() ?? 28.3228,
      isApproved: json['isApproved'] as bool? ?? true, // Default true for existing data
      approvalStatus: json['approvalStatus'] != null 
          ? ApplicationStatusExtension.fromString(json['approvalStatus'] as String)
          : ApplicationStatus.approved,
      submittedDate: json['submittedDate'] != null
          ? DateTime.parse(json['submittedDate'] as String)
          : DateTime.now(),
      approvedDate: json['approvedDate'] != null
          ? DateTime.parse(json['approvedDate'] as String)
          : null,
      viewCount: json['viewCount'] as int? ?? 0,
      likeCount: json['likeCount'] as int? ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'landlordId': landlordId,
      'title': title,
      'description': description,
      'imageUrls': imageUrls,
      'image': imageUrls.isNotEmpty ? imageUrls[0] : '', // Keep for backward compatibility
      'pricePerMonth': pricePerMonth,
      'location': location,
      'beds': beds,
      'baths': baths,
      'sqft': sqft,
      'rating': rating,
      'reviews': reviews,
      'type': type,
      'amenities': amenities,
      'landlord': landlordName,
      'landlordPhone': landlordPhone,
      'landlordEmail': landlordEmail,
      'occupied': occupied,
      'latitude': latitude,
      'longitude': longitude,
      'isApproved': isApproved,
      'approvalStatus': approvalStatus.toString().split('.').last,
      'submittedDate': submittedDate.toIso8601String(),
      'approvedDate': approvedDate?.toIso8601String(),
      'viewCount': viewCount,
      'likeCount': likeCount,
    };
  }

  // Getter for primary image (first image)
  String get primaryImage => imageUrls.isNotEmpty ? imageUrls[0] : '';
}