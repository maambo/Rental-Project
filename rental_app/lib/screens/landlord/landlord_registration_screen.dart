import 'package:flutter/material.dart';
import 'package:rental_app/models/landlord_tier.dart';
import 'package:rental_app/utils/constants.dart';

class LandlordRegistrationScreen extends StatefulWidget {
  const LandlordRegistrationScreen({super.key});

  @override
  State<LandlordRegistrationScreen> createState() => _LandlordRegistrationScreenState();
}

class _LandlordRegistrationScreenState extends State<LandlordRegistrationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _provinceController = TextEditingController();
  final _townController = TextEditingController();
  
  LandlordTier _selectedTier = LandlordTier.small;
  bool _acceptedTerms = false;
  final List<String> _uploadedDocuments = [];

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _provinceController.dispose();
    _townController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Landlord Registration'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildHeader(),
              const SizedBox(height: 24),
              _buildPersonalInfoSection(),
              const SizedBox(height: 24),
              _buildTierSelectionSection(),
              const SizedBox(height: 24),
              _buildDocumentsSection(),
              const SizedBox(height: 24),
              _buildTermsSection(),
              const SizedBox(height: 32),
              _buildSubmitButton(),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: kPrimaryGradient,
        borderRadius: BorderRadius.circular(12),
      ),
      child: const Row(
        children: [
          Icon(Icons.business, color: Colors.white, size: 32),
          SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Become a Landlord',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Join our platform and start renting your properties',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPersonalInfoSection() {
    return _buildSection(
      'Personal Information',
      Icons.person_outline,
      Column(
        children: [
          TextFormField(
            controller: _nameController,
            decoration: const InputDecoration(
              labelText: 'Full Name',
              hintText: 'Enter your full name',
              prefixIcon: Icon(Icons.person),
              border: OutlineInputBorder(),
            ),
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Please enter your name';
              }
              return null;
            },
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _emailController,
            decoration: const InputDecoration(
              labelText: 'Gmail Address',
              hintText: 'your.email@gmail.com',
              prefixIcon: Icon(Icons.email),
              border: OutlineInputBorder(),
            ),
            keyboardType: TextInputType.emailAddress,
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Please enter your email';
              }
              if (!value.contains('@gmail.com')) {
                return 'Please use a Gmail address';
              }
              return null;
            },
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _phoneController,
            decoration: const InputDecoration(
              labelText: 'Phone Number',
              hintText: '+260 XXX XXX XXX',
              prefixIcon: Icon(Icons.phone),
              border: OutlineInputBorder(),
            ),
            keyboardType: TextInputType.phone,
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Please enter your phone number';
              }
              return null;
            },
          ),
        ],
      ),
    );
  }

  Widget _buildTierSelectionSection() {
    return _buildSection(
      'Select Your Tier',
      Icons.business_center,
      Column(
        children: [
          _buildTierCard(
            LandlordTier.small,
            'Perfect for individual landlords',
            Icons.home,
          ),
          const SizedBox(height: 12),
          _buildTierCard(
            LandlordTier.medium,
            'Ideal for growing portfolios',
            Icons.apartment,
          ),
          const SizedBox(height: 12),
          _buildTierCard(
            LandlordTier.large,
            'For professional property managers',
            Icons.business,
          ),
        ],
      ),
    );
  }

  Widget _buildTierCard(LandlordTier tier, String description, IconData icon) {
    final isSelected = _selectedTier == tier;
    
    return InkWell(
      onTap: () {
        setState(() {
          _selectedTier = tier;
        });
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isSelected
              ? kPrimaryRed.withAlpha((255 * 0.1).round())
              : kBackgroundColorLight,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected
                ? kPrimaryRed
                : kTextColorGrey.withAlpha((255 * 0.2).round()),
            width: isSelected ? 2 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isSelected
                    ? kPrimaryRed
                    : kTextColorGrey.withAlpha((255 * 0.2).round()),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                icon,
                color: isSelected ? Colors.white : kTextColorGrey,
                size: 24,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tier.displayName,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: isSelected ? kPrimaryRed : kTextColorBlack,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    description,
                    style: TextStyle(
                      fontSize: 12,
                      color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.check_circle,
                        size: 16,
                        color: isSelected ? kPrimaryRed : kTextColorGrey,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        tier.propertyLimit == -1
                            ? 'Unlimited properties'
                            : 'Up to ${tier.propertyLimit} properties',
                        style: TextStyle(
                          fontSize: 12,
                          color: kTextColorGrey.withAlpha((255 * 0.8).round()),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            if (isSelected)
              const Icon(Icons.check_circle, color: kPrimaryRed, size: 28),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentsSection() {
    return _buildSection(
      'Required Documents',
      Icons.folder_outlined,
      Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Please upload the following documents:',
            style: TextStyle(
              fontSize: 14,
              color: kTextColorGrey.withAlpha((255 * 0.8).round()),
            ),
          ),
          const SizedBox(height: 12),
          _buildDocumentItem('National ID or Passport'),
          _buildDocumentItem('Proof of Property Ownership'),
          _buildDocumentItem('Tax Clearance Certificate (Optional)'),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: _uploadDocument,
            icon: const Icon(Icons.upload_file),
            label: const Text('Upload Documents'),
            style: OutlinedButton.styleFrom(
              foregroundColor: kPrimaryRed,
              side: const BorderSide(color: kPrimaryRed),
              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
            ),
          ),
          if (_uploadedDocuments.isNotEmpty) ...[
            const SizedBox(height: 16),
            ...(_uploadedDocuments.map((doc) => _buildUploadedDocument(doc))),
          ],
        ],
      ),
    );
  }

  Widget _buildDocumentItem(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          const Icon(Icons.description, size: 20, color: kPrimaryRed),
          const SizedBox(width: 8),
          Text(
            title,
            style: const TextStyle(fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildUploadedDocument(String filename) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.green.withAlpha((255 * 0.1).round()),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.green.withAlpha((255 * 0.3).round())),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle, color: Colors.green, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              filename,
              style: const TextStyle(fontSize: 14),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.close, size: 20),
            onPressed: () {
              setState(() {
                _uploadedDocuments.remove(filename);
              });
            },
          ),
        ],
      ),
    );
  }

  Widget _buildTermsSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: kBackgroundColorLight,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: kTextColorGrey.withAlpha((255 * 0.2).round()),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Application Fee',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: kTextColorBlack,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'The application fee will be determined by the admin based on your selected tier and will be communicated to you upon review.',
            style: TextStyle(
              fontSize: 14,
              color: kTextColorGrey.withAlpha((255 * 0.8).round()),
            ),
          ),
          const SizedBox(height: 16),
          CheckboxListTile(
            value: _acceptedTerms,
            onChanged: (value) {
              setState(() {
                _acceptedTerms = value ?? false;
              });
            },
            title: const Text(
              'I accept the terms and conditions',
              style: TextStyle(fontSize: 14),
            ),
            controlAffinity: ListTileControlAffinity.leading,
            contentPadding: EdgeInsets.zero,
            activeColor: kPrimaryRed,
          ),
        ],
      ),
    );
  }

  Widget _buildSubmitButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _acceptedTerms ? _submitApplication : null,
        style: ElevatedButton.styleFrom(
          backgroundColor: kPrimaryRed,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          disabledBackgroundColor: kTextColorGrey.withAlpha((255 * 0.3).round()),
        ),
        child: const Text(
          'Submit Application',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }

  Widget _buildSection(String title, IconData icon, Widget content) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha((255 * 0.05).round()),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: kPrimaryRed, size: 24),
              const SizedBox(width: 12),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: kTextColorBlack,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          content,
        ],
      ),
    );
  }

  void _uploadDocument() {
    // TODO: Implement document upload
    setState(() {
      _uploadedDocuments.add('Document_${_uploadedDocuments.length + 1}.pdf');
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Document uploaded successfully'),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _submitApplication() {
    if (_formKey.currentState!.validate()) {
      if (_uploadedDocuments.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Please upload at least one document'),
            backgroundColor: kPrimaryRed,
          ),
        );
        return;
      }

      // TODO: Implement application submission
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Application Submitted'),
          content: const Text(
            'Your landlord application has been submitted successfully. '
            'You will receive an email notification once it has been reviewed by our admin team.',
          ),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                Navigator.pop(context);
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
