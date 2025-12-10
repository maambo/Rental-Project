import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class DashboardStatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;
  final String? subtitle;
  final VoidCallback? onTap;
  final String? trend;
  final bool? trendUp;

  const DashboardStatCard({
    super.key,
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
    this.subtitle,
    this.onTap,
    this.trend,
    this.trendUp,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      clipBehavior: Clip.antiAlias, // Clip ink splash and decoration
      child: InkWell(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            gradient: LinearGradient(
              colors: [
                color.withAlpha((255 * 0.1).round()),
                color.withAlpha((255 * 0.05).round()),
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: Stack(
            children: [
              // Decorative background circle
              Positioned(
                right: -20,
                top: -20,
                child: Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    color: color.withAlpha((255 * 0.05).round()),
                    shape: BoxShape.circle,
                  ),
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: color.withAlpha((255 * 0.2).round()),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          icon,
                          color: color,
                          size: 24,
                        ),
                      ),
                      if (onTap != null)
                        Icon(
                          Icons.arrow_forward_ios,
                          color: color.withAlpha((255 * 0.5).round()),
                          size: 16,
                        ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Flexible(
                    child: FittedBox(
                      fit: BoxFit.scaleDown,
                      alignment: Alignment.centerLeft,
                      child: Text(
                        value,
                        style: TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                          color: color,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 13,
                      color: kTextColorGrey,
                      fontWeight: FontWeight.w500,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  if (subtitle != null || trend != null) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        if (trend != null) ...[
                          Icon(
                            trendUp == true ? Icons.trending_up : Icons.trending_down,
                            color: trendUp == true ? Colors.green : Colors.red,
                            size: 16,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            trend!,
                            style: TextStyle(
                              fontSize: 12,
                              color: trendUp == true ? Colors.green : Colors.red,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (subtitle != null)
                            const SizedBox(width: 8), // Spacer between trend and subtitle
                        ],
                        if (subtitle != null)
                          Expanded(
                            child: Text(
                              subtitle!,
                              style: TextStyle(
                                fontSize: 11,
                                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                      ],
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
