import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class PropertyStatWidget extends StatelessWidget {
  final IconData icon;
  final String text;
  final bool isDetailed;

  const PropertyStatWidget({
    super.key,
    required this.icon,
    required this.text,
    this.isDetailed = false,
  });

  @override
  Widget build(BuildContext context) {
    if (isDetailed) {
      return Column(
        children: [
          Icon(icon, color: kPrimaryRed, size: 28),
          const SizedBox(height: 4),
          Text(
            text.split(' ')[0],
            style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: kTextColorBlack),
          ),
          Text(
            text.split(' ')[1],
            style: const TextStyle(fontSize: 12, color: kTextColorGrey),
          ),
        ],
      );
    } else {
      return Row(
        children: [
          Icon(icon, color: kTextColorGrey, size: 16),
          const SizedBox(width: 4),
          Text(text,
              style: const TextStyle(color: kTextColorGrey, fontSize: 12)),
        ],
      );
    }
  }
}
