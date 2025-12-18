import 'package:flutter/material.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/models/landlord_tier.dart';
import 'package:rental_app/models/application_status.dart';
import 'package:rental_app/utils/constants.dart';

class ManageUsersScreen extends StatefulWidget {
  final UserRole? initialFilterRole;

  const ManageUsersScreen({super.key, this.initialFilterRole});

  @override
  State<ManageUsersScreen> createState() => _ManageUsersScreenState();
}

class _ManageUsersScreenState extends State<ManageUsersScreen> {
  UserRole? _filterRole;
  
  // TODO: Replace with actual data
  final List<User> _mockUsers = [];

  @override
  void initState() {
    super.initState();
    _filterRole = widget.initialFilterRole;
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Manage Users'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
          ),
        ],
      ),
      body: _buildUsersList(),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showUserForm(null),
        icon: const Icon(Icons.person_add),
        label: const Text('Add User'),
        backgroundColor: kPrimaryRed,
      ),
    );
  }

  Widget _buildUsersList() {
    var filteredUsers = _mockUsers.where((user) {
      if (_filterRole != null && user.role != _filterRole) {
        return false;
      }
      return true;
    }).toList();

    if (filteredUsers.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.people_outline,
              size: 64,
              color: kTextColorGrey.withAlpha((255 * 0.5).round()),
            ),
            const SizedBox(height: 16),
            Text(
              _filterRole != null
                  ? 'No ${_filterRole!.name}s found'
                  : 'No users yet',
              style: TextStyle(
                fontSize: 16,
                color: kTextColorGrey.withAlpha((255 * 0.7).round()),
              ),
            ),
            if (_filterRole != null) ...[
              const SizedBox(height: 12),
              TextButton.icon(
                onPressed: () {
                  setState(() {
                    _filterRole = null;
                  });
                },
                icon: const Icon(Icons.clear),
                label: const Text('Clear Filter'),
              ),
            ],
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredUsers.length,
      itemBuilder: (context, index) {
        final user = filteredUsers[index];
        return _buildUserCard(user);
      },
    );
  }

  Widget _buildUserCard(User user) {
    Color roleColor;
    IconData roleIcon;
    
    switch (user.role) {
      case UserRole.admin:
        roleColor = kPrimaryRed;
        roleIcon = Icons.admin_panel_settings;
        break;
      case UserRole.landlord:
        roleColor = Colors.purple;
        roleIcon = Icons.business;
        break;
      case UserRole.tenant:
        roleColor = Colors.blue;
        roleIcon = Icons.person;
        break;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        leading: CircleAvatar(
          radius: 30,
          backgroundColor: roleColor.withAlpha((255 * 0.2).round()),
          child: Icon(roleIcon, color: roleColor, size: 28),
        ),
        title: Text(
          user.name,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: kTextColorBlack,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              user.email,
              style: TextStyle(
                fontSize: 14,
                color: kTextColorGrey.withAlpha((255 * 0.8).round()),
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: roleColor.withAlpha((255 * 0.1).round()),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    user.role.name.toUpperCase(),
                    style: TextStyle(
                      color: roleColor,
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                if (user.role == UserRole.landlord && user.landlordTier != null) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.amber.withAlpha((255 * 0.1).round()),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      user.landlordTier!.displayName,
                      style: const TextStyle(
                        color: Colors.amber,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
        trailing: PopupMenuButton<String>(
          icon: const Icon(Icons.more_vert),
          onSelected: (value) {
            if (value == 'edit') {
              _showUserForm(user);
            } else if (value == 'delete') {
              _showDeleteDialog(user);
            }
          },
          itemBuilder: (context) => [
            const PopupMenuItem(
              value: 'edit',
              child: Row(
                children: [
                  Icon(Icons.edit, size: 20),
                  SizedBox(width: 8),
                  Text('Edit'),
                ],
              ),
            ),
            const PopupMenuItem(
              value: 'delete',
              child: Row(
                children: [
                  Icon(Icons.delete, size: 20, color: kPrimaryRed),
                  SizedBox(width: 8),
                  Text('Delete', style: TextStyle(color: kPrimaryRed)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter by Role'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.clear),
              title: const Text('All Users'),
              selected: _filterRole == null,
              onTap: () {
                setState(() {
                  _filterRole = null;
                });
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.admin_panel_settings, color: kPrimaryRed),
              title: const Text('Admins'),
              selected: _filterRole == UserRole.admin,
              onTap: () {
                setState(() {
                  _filterRole = UserRole.admin;
                });
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.business, color: Colors.purple),
              title: const Text('Landlords'),
              selected: _filterRole == UserRole.landlord,
              onTap: () {
                setState(() {
                  _filterRole = UserRole.landlord;
                });
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.person, color: Colors.blue),
              title: const Text('Tenants'),
              selected: _filterRole == UserRole.tenant,
              onTap: () {
                setState(() {
                  _filterRole = UserRole.tenant;
                });
                Navigator.pop(context);
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showUserForm(User? user) {
    final isEdit = user != null;
    final TextEditingController nameController = TextEditingController(text: user?.name ?? '');
    final TextEditingController emailController = TextEditingController(text: user?.email ?? '');
    final TextEditingController passwordController = TextEditingController();
    UserRole selectedRole = user?.role ?? UserRole.tenant;
    LandlordTier? selectedTier = user?.landlordTier;
    ApplicationStatus? selectedStatus = user?.applicationStatus;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            title: Text(isEdit ? 'Edit User' : 'Add New User'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  TextField(
                    controller: nameController,
                    decoration: const InputDecoration(
                      labelText: 'Full Name *',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.person),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(
                      labelText: 'Email *',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.email),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: passwordController,
                    obscureText: true,
                    decoration: InputDecoration(
                      labelText: isEdit ? 'New Password (optional)' : 'Password *',
                      border: const OutlineInputBorder(),
                      prefixIcon: const Icon(Icons.lock),
                    ),
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<UserRole>(
                    value: selectedRole,
                    decoration: const InputDecoration(
                      labelText: 'Role *',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.badge),
                    ),
                    items: UserRole.values.map((role) {
                      return DropdownMenuItem(
                        value: role,
                        child: Text(role.name.toUpperCase()),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setDialogState(() {
                        selectedRole = value!;
                        if (selectedRole != UserRole.landlord) {
                          selectedTier = null;
                          selectedStatus = null;
                        }
                      });
                    },
                  ),
                  if (selectedRole == UserRole.landlord) ...[
                    const SizedBox(height: 16),
                    DropdownButtonFormField<LandlordTier>(
                      value: selectedTier,
                      decoration: const InputDecoration(
                        labelText: 'Landlord Tier',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.business),
                      ),
                      items: LandlordTier.values.map((tier) {
                        return DropdownMenuItem(
                          value: tier,
                          child: Text(tier.displayName),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setDialogState(() {
                          selectedTier = value;
                        });
                      },
                    ),
                    const SizedBox(height: 16),
                    DropdownButtonFormField<ApplicationStatus>(
                      value: selectedStatus,
                      decoration: const InputDecoration(
                        labelText: 'Application Status',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.approval),
                      ),
                      items: ApplicationStatus.values.map((status) {
                        return DropdownMenuItem(
                          value: status,
                          child: Text(status.displayName),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setDialogState(() {
                          selectedStatus = value;
                        });
                      },
                    ),
                  ],
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Cancel'),
              ),
              ElevatedButton(
                onPressed: () {
                  if (nameController.text.isEmpty || emailController.text.isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Please fill in all required fields'),
                        backgroundColor: kPrimaryRed,
                      ),
                    );
                    return;
                  }
                  if (!isEdit && passwordController.text.isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Password is required for new users'),
                        backgroundColor: kPrimaryRed,
                      ),
                    );
                    return;
                  }
                  // TODO: Implement save user
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(isEdit ? 'User updated successfully' : 'User created successfully'),
                      backgroundColor: Colors.green,
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimaryRed,
                  foregroundColor: Colors.white,
                ),
                child: Text(isEdit ? 'Update' : 'Create'),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showDeleteDialog(User user) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete User'),
        content: Text('Are you sure you want to delete ${user.name}? This action cannot be undone.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              // TODO: Implement delete user
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('User deleted successfully'),
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
}
