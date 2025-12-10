import 'package:flutter/material.dart';
import 'package:rental_app/models/property_model.dart';
import 'package:rental_app/utils/constants.dart';

class PropertyApplicationScreen extends StatefulWidget {
  final Property property;

  const PropertyApplicationScreen({super.key, required this.property});

  @override
  State<PropertyApplicationScreen> createState() => _PropertyApplicationScreenState();
}

class _PropertyApplicationScreenState extends State<PropertyApplicationScreen> {
  final _formKey = GlobalKey<FormState>();
  
  // Personal Information
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  
  // Employment Details
  final _employerController = TextEditingController();
  final _jobTitleController = TextEditingController();
  final _incomeController = TextEditingController();
  final _employmentDurationController = TextEditingController();
  
  // References
  final _ref1NameController = TextEditingController();
  final _ref1PhoneController = TextEditingController();
  final _ref1RelationController = TextEditingController();
  final _ref2NameController = TextEditingController();
  final _ref2PhoneController = TextEditingController();
  final _ref2RelationController = TextEditingController();
  
  // Additional
  DateTime? _moveInDate;
  final _leaseDurationController = TextEditingController();
  final _occupantsController = TextEditingController();
  bool _hasPets = false;
  final _petDetailsController = TextEditingController();
  final _commentsController = TextEditingController();
  
  bool _isSubmitting = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _employerController.dispose();
    _jobTitleController.dispose();
    _incomeController.dispose();
    _employmentDurationController.dispose();
    _ref1NameController.dispose();
    _ref1PhoneController.dispose();
    _ref1RelationController.dispose();
    _ref2NameController.dispose();
    _ref2PhoneController.dispose();
    _ref2RelationController.dispose();
    _leaseDurationController.dispose();
    _occupantsController.dispose();
    _petDetailsController.dispose();
    _commentsController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Property Application'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildPropertyInfo(),
              const SizedBox(height: 24),
              _buildSectionTitle('Personal Information'),
              _buildTextField(_nameController, 'Full Name', Icons.person, required: true),
              _buildTextField(_emailController, 'Email', Icons.email, keyboardType: TextInputType.emailAddress, required: true),
              _buildTextField(_phoneController, 'Phone Number', Icons.phone, keyboardType: TextInputType.phone, required: true),
              _buildTextField(_addressController, 'Current Address', Icons.home, required: true),
              const SizedBox(height: 24),
              _buildSectionTitle('Employment Details'),
              _buildTextField(_employerController, 'Employer Name', Icons.business, required: true),
              _buildTextField(_jobTitleController, 'Job Title', Icons.work, required: true),
              _buildTextField(_incomeController, 'Monthly Income (K)', Icons.attach_money, keyboardType: TextInputType.number, required: true),
              _buildTextField(_employmentDurationController, 'Employment Duration', Icons.calendar_today, required: true),
              const SizedBox(height: 24),
              _buildSectionTitle('References'),
              const Text('Reference 1', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              _buildTextField(_ref1NameController, 'Name', Icons.person, required: true),
              _buildTextField(_ref1PhoneController, 'Phone', Icons.phone, keyboardType: TextInputType.phone, required: true),
              _buildTextField(_ref1RelationController, 'Relationship', Icons.people, required: true),
              const SizedBox(height: 16),
              const Text('Reference 2', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              _buildTextField(_ref2NameController, 'Name', Icons.person, required: true),
              _buildTextField(_ref2PhoneController, 'Phone', Icons.phone, keyboardType: TextInputType.phone, required: true),
              _buildTextField(_ref2RelationController, 'Relationship', Icons.people, required: true),
              const SizedBox(height: 24),
              _buildSectionTitle('Additional Information'),
              _buildDatePicker(),
              const SizedBox(height: 16),
              _buildTextField(_leaseDurationController, 'Preferred Lease Duration', Icons.event, required: true),
              _buildTextField(_occupantsController, 'Number of Occupants', Icons.group, keyboardType: TextInputType.number, required: true),
              const SizedBox(height: 16),
              CheckboxListTile(
                title: const Text('Do you have pets?'),
                value: _hasPets,
                onChanged: (value) {
                  setState(() {
                    _hasPets = value ?? false;
                  });
                },
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              if (_hasPets) ...[
                const SizedBox(height: 8),
                _buildTextField(_petDetailsController, 'Pet Details (Type, Breed, Size)', Icons.pets),
              ],
              const SizedBox(height: 16),
              TextFormField(
                controller: _commentsController,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Additional Comments',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.comment),
                ),
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitApplication,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kPrimaryRed,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        )
                      : const Text('Submit Application'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPropertyInfo() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: kTextColorGrey.withAlpha((255 * 0.1).round()),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: Image.network(
              widget.property.primaryImage,
              width: 60,
              height: 60,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) {
                return Container(
                  width: 60,
                  height: 60,
                  color: kTextColorGrey.withAlpha((255 * 0.3).round()),
                  child: const Icon(Icons.image),
                );
              },
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.property.title,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'K${widget.property.price.toStringAsFixed(0)}/month',
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: kPrimaryRed,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.bold,
          color: kTextColorBlack,
        ),
      ),
    );
  }

  Widget _buildTextField(
    TextEditingController controller,
    String label,
    IconData icon, {
    TextInputType keyboardType = TextInputType.text,
    bool required = false,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        decoration: InputDecoration(
          labelText: required ? '$label *' : label,
          border: const OutlineInputBorder(),
          prefixIcon: Icon(icon),
        ),
        validator: required
            ? (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'This field is required';
                }
                if (label.contains('Email') && !value.contains('@')) {
                  return 'Please enter a valid email';
                }
                return null;
              }
            : null,
      ),
    );
  }

  Widget _buildDatePicker() {
    return InkWell(
      onTap: () async {
        final date = await showDatePicker(
          context: context,
          initialDate: DateTime.now().add(const Duration(days: 30)),
          firstDate: DateTime.now(),
          lastDate: DateTime.now().add(const Duration(days: 365)),
        );
        if (date != null) {
          setState(() {
            _moveInDate = date;
          });
        }
      },
      child: InputDecorator(
        decoration: const InputDecoration(
          labelText: 'Preferred Move-in Date *',
          border: OutlineInputBorder(),
          prefixIcon: Icon(Icons.calendar_today),
        ),
        child: Text(
          _moveInDate == null
              ? 'Select a date'
              : '${_moveInDate!.day}/${_moveInDate!.month}/${_moveInDate!.year}',
          style: TextStyle(
            color: _moveInDate == null
                ? kTextColorGrey.withAlpha((255 * 0.6).round())
                : kTextColorBlack,
          ),
        ),
      ),
    );
  }

  Future<void> _submitApplication() async {
    if (!_formKey.currentState!.validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please fill in all required fields'),
          backgroundColor: kPrimaryRed,
        ),
      );
      return;
    }

    if (_moveInDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select a move-in date'),
          backgroundColor: kPrimaryRed,
        ),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    // TODO: Submit application to backend
    await Future.delayed(const Duration(seconds: 2));

    if (mounted) {
      setState(() {
        _isSubmitting = false;
      });

      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Application Submitted!'),
          content: const Text(
            'Your rental application has been submitted successfully. The landlord will review your application and contact you soon.',
          ),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context); // Close dialog
                Navigator.pop(context); // Go back to property details
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: kPrimaryRed,
                foregroundColor: Colors.white,
              ),
              child: const Text('OK'),
            ),
          ],
        ),
      );
    }
  }
}
