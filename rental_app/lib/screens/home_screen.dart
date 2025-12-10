import 'package:flutter/material.dart';
import 'package:rental_app/widgets/global_drawer.dart';
import 'package:rental_app/models/user_model.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rental App'),
        centerTitle: true,
      ),
      drawer: const GlobalDrawer(userRole: UserRole.tenant),
      body: const Center(
        child: PropertyList(),
      ),
    );
  }
}

class PropertyList extends StatelessWidget {
  const PropertyList({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: 10, // Replace with actual data
      itemBuilder: (context, index) {
        return Card(
          margin: const EdgeInsets.all(8.0),
          child: ListTile(
            leading: const Icon(Icons.house),
            title: Text('Property ${index + 1}'),
            subtitle: const Text('Property description'),
            trailing: const Icon(Icons.arrow_forward_ios),
            onTap: () {
              // Navigate to property details screen
            },
          ),
        );
      },
    );
  }
}