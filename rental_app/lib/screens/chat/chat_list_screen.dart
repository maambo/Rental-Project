import 'package:flutter/material.dart';
import 'package:rental_app/utils/constants.dart';

class ChatListScreen extends StatelessWidget {
  const ChatListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    // Mock data
    final List<Map<String, dynamic>> chats = [
      {
        'name': 'John Landlord',
        'lastMessage': 'When can you come for the viewing?',
        'time': '10:30 AM',
        'unread': 2,
        'avatarColor': Colors.blue,
      },
      {
        'name': 'Alice Property Manager',
        'lastMessage': 'The maintenance request has been approved.',
        'time': 'Yesterday',
        'unread': 0,
        'avatarColor': Colors.purple,
      },
       {
        'name': 'Support',
        'lastMessage': 'Ticket #1234 resolved.',
        'time': 'Mon',
        'unread': 0,
        'avatarColor': kPrimaryRed,
      },
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Messages'),
      ),
      body: ListView.separated(
        itemCount: chats.length,
        separatorBuilder: (context, index) => const Divider(height: 1),
        itemBuilder: (context, index) {
          final chat = chats[index];
          return ListTile(
            leading: CircleAvatar(
              backgroundColor: chat['avatarColor'].withAlpha(50),
              child: Text(
                (chat['name'] as String)[0],
                style: TextStyle(color: chat['avatarColor'], fontWeight: FontWeight.bold),
              ),
            ),
            title: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  chat['name'],
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
                Text(
                  chat['time'],
                  style: TextStyle(
                    fontSize: 12,
                    color: kTextColorGrey.withAlpha(150),
                  ),
                ),
              ],
            ),
            subtitle: Text(
              chat['lastMessage'],
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: chat['unread'] > 0 ? kTextColorBlack : kTextColorGrey,
                fontWeight: chat['unread'] > 0 ? FontWeight.bold : FontWeight.normal,
              ),
            ),
            trailing: chat['unread'] > 0
                ? Container(
                    padding: const EdgeInsets.all(6),
                    decoration: const BoxDecoration(
                      color: kPrimaryRed,
                      shape: BoxShape.circle,
                    ),
                    child: Text(
                      chat['unread'].toString(),
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  )
                : null,
            onTap: () {
              Navigator.pushNamed(
                context,
                '/chat/detail',
                arguments: {'name': chat['name']},
              );
            },
          );
        },
      ),
    );
  }
}
