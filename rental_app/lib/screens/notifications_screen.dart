import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    // Mock data
    final List<Map<String, dynamic>> notifications = [
      {
        'title': 'Rent Due Reminder',
        'body': 'Your rent for December is due in 3 days.',
        'time': '2 hours ago',
        'isRead': false,
        'icon': Icons.payment,
        'color': kPrimaryRed,
      },
      {
        'title': 'Maintenance Scheduled',
        'body': 'The plumber is scheduled to visit on Dec 15th at 10 AM.',
        'time': 'Yesterday',
        'isRead': true,
        'icon': Icons.build,
        'color': kPrimaryOrange,
      },
      {
        'title': 'New Message',
        'body': 'You have a new message from John Landlord.',
        'time': '2 days ago',
        'isRead': true,
        'icon': Icons.message,
        'color': Colors.blue,
      },
       {
        'title': 'Profile Update',
        'body': 'Your profile was successfully updated.',
        'time': 'Last week',
        'isRead': true,
        'icon': Icons.person,
        'color': Colors.green,
      },
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          IconButton(
            icon: const Icon(Icons.done_all),
            onPressed: () {
              // TODO: Mark all as read
            },
            tooltip: 'Mark all as read',
          ),
        ],
      ),
      body: notifications.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.notifications_off_outlined, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No notifications yet',
                    style: TextStyle(color: Colors.grey[600], fontSize: 16),
                  ),
                ],
              ),
            )
          : ListView.separated(
              itemCount: notifications.length,
              separatorBuilder: (context, index) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final notification = notifications[index];
                final isRead = notification['isRead'] as bool;

                return Container(
                  color: isRead ? null : kPrimaryRed.withAlpha(10),
                  child: ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: (notification['color'] as Color).withAlpha(30),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        notification['icon'] as IconData,
                        color: notification['color'] as Color,
                        size: 24,
                      ),
                    ),
                    title: Text(
                      notification['title'],
                      style: TextStyle(
                        fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                      ),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text(
                          notification['body'],
                          style: TextStyle(
                            color: kTextColorBlack.withAlpha(200),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          notification['time'],
                          style: TextStyle(
                            fontSize: 12,
                            color: kTextColorGrey.withAlpha(150),
                          ),
                        ),
                      ],
                    ),
                    onTap: () {
                      // TODO: Mark as read and navigate if necessary
                    },
                  ),
                );
              },
            ),
    );
  }
}
