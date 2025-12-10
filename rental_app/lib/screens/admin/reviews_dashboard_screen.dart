import 'package:flutter/material.dart';
import 'package:rental_app/models/property_review.dart';
import 'package:rental_app/utils/constants.dart';

class ReviewsDashboardScreen extends StatefulWidget {
  const ReviewsDashboardScreen({super.key});

  @override
  State<ReviewsDashboardScreen> createState() => _ReviewsDashboardScreenState();
}

class _ReviewsDashboardScreenState extends State<ReviewsDashboardScreen> {
  String _sortBy = 'recent'; // recent, rating_high, rating_low
  
  // TODO: Replace with actual service call
  final List<PropertyReview> _mockReviews = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Reviews Dashboard'),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.sort),
            onSelected: (value) {
              setState(() {
                _sortBy = value;
              });
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'recent',
                child: Text('Most Recent'),
              ),
              const PopupMenuItem(
                value: 'rating_high',
                child: Text('Highest Rating'),
              ),
              const PopupMenuItem(
                value: 'rating_low',
                child: Text('Lowest Rating'),
              ),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          _buildStatsHeader(),
          Expanded(child: _buildReviewsList()),
        ],
      ),
    );
  }

  Widget _buildStatsHeader() {
    // TODO: Calculate from actual data
    final totalReviews = _mockReviews.length;
    final averageRating = _mockReviews.isEmpty
        ? 0.0
        : _mockReviews.map((r) => r.rating).reduce((a, b) => a + b) / totalReviews;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: kPrimaryGradient,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha((255 * 0.1).round()),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: _buildStatCard(
              'Total Reviews',
              totalReviews.toString(),
              Icons.rate_review,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: _buildStatCard(
              'Avg Rating',
              averageRating.toStringAsFixed(1),
              Icons.star,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white.withAlpha((255 * 0.2).round()),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          Icon(icon, color: Colors.white, size: 32),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              fontSize: 12,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildReviewsList() {
    if (_mockReviews.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.rate_review_outlined,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              'No reviews yet',
              style: TextStyle(
                fontSize: 16,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
          ],
        ),
      );
    }

    final sortedReviews = List<PropertyReview>.from(_mockReviews);
    switch (_sortBy) {
      case 'rating_high':
        sortedReviews.sort((a, b) => b.rating.compareTo(a.rating));
        break;
      case 'rating_low':
        sortedReviews.sort((a, b) => a.rating.compareTo(b.rating));
        break;
      case 'recent':
      default:
        sortedReviews.sort((a, b) => b.createdDate.compareTo(a.createdDate));
        break;
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: sortedReviews.length,
      itemBuilder: (context, index) {
        final review = sortedReviews[index];
        return _buildReviewCard(review);
      },
    );
  }

  Widget _buildReviewCard(PropertyReview review) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: kPrimaryRed.withAlpha((255 * 0.1).round()),
                  child: Text(
                    review.userName[0].toUpperCase(),
                    style: const TextStyle(
                      color: kPrimaryRed,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        review.userName,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: kTextColorBlack,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _formatDate(review.createdDate),
                        style: TextStyle(
                          fontSize: 12,
                          color: kTextColorGrey.withAlpha((255 * 0.6).round()),
                        ),
                      ),
                    ],
                  ),
                ),
                _buildRatingStars(review.rating),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              review.comment,
              style: TextStyle(
                fontSize: 14,
                color: kTextColorGrey.withAlpha((255 * 0.9).round()),
                height: 1.5,
              ),
            ),
            if (review.updatedDate != null) ...[
              const SizedBox(height: 8),
              Text(
                'Updated on ${_formatDate(review.updatedDate!)}',
                style: TextStyle(
                  fontSize: 12,
                  fontStyle: FontStyle.italic,
                  color: kTextColorGrey.withAlpha((255 * 0.6).round()),
                ),
              ),
            ],
            const SizedBox(height: 12),
            const Divider(height: 1),
            const SizedBox(height: 12),
            Row(
              children: [
                TextButton.icon(
                  onPressed: () {
                    // TODO: Navigate to property details
                  },
                  icon: const Icon(Icons.home_outlined, size: 18),
                  label: const Text('View Property'),
                ),
                const Spacer(),
                IconButton(
                  onPressed: () => _showDeleteDialog(review),
                  icon: const Icon(Icons.delete_outline, color: kPrimaryRed),
                  tooltip: 'Delete Review',
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRatingStars(double rating) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(5, (index) {
        return Icon(
          index < rating.floor() ? Icons.star : Icons.star_border,
          color: Colors.amber,
          size: 20,
        );
      }),
    );
  }

  void _showDeleteDialog(PropertyReview review) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Review'),
        content: Text(
          'Are you sure you want to delete this review by ${review.userName}? This action cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              // TODO: Implement delete logic
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Review deleted'),
                  backgroundColor: kPrimaryRed,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: kPrimaryRed,
              foregroundColor: Colors.white,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
