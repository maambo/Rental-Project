import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:rental_app/models/user_model.dart';
import 'package:rental_app/utils/constants.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _showPassword = false;
  bool _rememberMe = false;
  UserRole _selectedRole = UserRole.tenant;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: LayoutBuilder(
        builder: (context, constraints) {
          if (constraints.maxWidth >= 800) {
            return _buildDesktopLayout();
          } else {
            return _buildMobileLayout();
          }
        },
      ),
    );
  }

  Widget _buildMobileLayout() {
    return DecoratedBox(
      decoration: const BoxDecoration(
        color: kBackgroundColorLight, 
      ),
      child: SingleChildScrollView(
        child: Column(
          children: [
            _buildHeader(),
            _buildForm(),
            const SizedBox(height: 24),
            const Text(
              '© 2025 Rental App. All rights reserved.',
              style: TextStyle(color: kTextColorGrey, fontSize: 12),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildDesktopLayout() {
    return Row(
      children: [
        Expanded(
          flex: 1,
          child: _buildDesktopBanner(),
        ),
        Expanded(
          flex: 1,
          child: Container(
            color: kTextColorWhite,
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(32.0),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 450),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        'Welcome Back',
                        style: TextStyle(
                          color: kTextColorBlack,
                          fontSize: 32,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Please enter your details to sign in',
                        style: TextStyle(color: kTextColorGrey, fontSize: 16),
                      ),
                      const SizedBox(height: 32),
                      _buildForm(),
                      const SizedBox(height: 24),
                      const Text(
                        '© 2025 Rental App. All rights reserved.',
                        style: TextStyle(color: kTextColorGrey, fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildDesktopBanner() {
    return Container(
      decoration: const BoxDecoration(
        gradient: kPrimaryGradient,
      ),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: kTextColorWhite.withAlpha((255 * 0.2).round()),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(Icons.home, color: kTextColorWhite, size: 64),
            ),
            const SizedBox(height: 32),
            const Text(
              'Rental App',
              style: TextStyle(
                color: kTextColorWhite,
                fontSize: 48,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Find your perfect home today',
              style: TextStyle(
                color: kTextColorWhite.withAlpha((255 * 0.9).round()),
                fontSize: 24,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCard() {
    return Container(
      decoration: BoxDecoration(
        color: kTextColorWhite,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha((255 * 0.2).round()),
            blurRadius: 20,
            spreadRadius: 5,
          ),
        ],
      ),
      child: Column(
        children: [
          _buildHeader(),
          _buildForm(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(32, 64, 32, 32),
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: kPrimaryGradient,
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(32),
          bottomRight: Radius.circular(32),
        ),
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: kTextColorWhite.withAlpha((255 * 0.2).round()),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(Icons.home, color: kTextColorWhite, size: 40),
          ),
          const SizedBox(height: 16),
          const Text(
            'Welcome Back',
            style: TextStyle(
              color: kTextColorWhite,
              fontSize: 28,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Sign in to find your perfect rental',
            style: TextStyle(
              color: kTextColorWhite.withAlpha((255 * 0.9).round()),
              fontSize: 16,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildForm() {
    return Padding(
      padding: const EdgeInsets.all(32.0),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _buildRoleDropdown(),
            const SizedBox(height: 28),
            _buildEmailField(),
            const SizedBox(height: 28),
            _buildPasswordField(),
            const SizedBox(height: 28),
            _buildOptions(),
            const SizedBox(height: 28),
            _buildSignInButton(),
            const SizedBox(height: 28),
            _buildDivider(),
            const SizedBox(height: 28),
            _buildSocialLogins(),
            const SizedBox(height: 32),
            _buildSignUpLink(),
          ],
        ),
      ),
    );
  }

  Widget _buildRoleDropdown() {
    return DropdownButtonFormField<UserRole>(
      initialValue: _selectedRole,
      style: const TextStyle(color: kTextColorWhite),
      items: UserRole.values
          .map((role) => DropdownMenuItem(
                value: role,
                child: Text(role.name[0].toUpperCase() + role.name.substring(1)),
              ))
          .toList(),
      onChanged: (value) {
        if (value != null) {
          setState(() {
            _selectedRole = value;
          });
        }
      },
      decoration: InputDecoration(
        labelText: 'I am a...',
        prefixIcon: const Icon(Icons.person_outline, color: kTextColorGrey),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.grey, width: 1.0),
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: kPrimaryRed, width: 2),
        ),
      ),
    );
  }

  Widget _buildEmailField() {
    return TextFormField(
      controller: _emailController,
      keyboardType: TextInputType.emailAddress,
      style: const TextStyle(color: kTextColorWhite),
      decoration: InputDecoration(
        labelText: 'Email Address',
        prefixIcon: const Icon(Icons.mail_outline, color: kTextColorGrey),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.grey, width: 1.0),
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: kPrimaryRed, width: 2),
        ),
      ),
      validator: (value) {
        if (value == null || value.isEmpty || !value.contains('@')) {
          return 'Please enter a valid email';
        }
        return null;
      },
    );
  }

  Widget _buildPasswordField() {
    return TextFormField(
      controller: _passwordController,
      obscureText: !_showPassword,
      style: const TextStyle(color: kTextColorWhite),
      decoration: InputDecoration(
        labelText: 'Password',
        prefixIcon: const Icon(Icons.lock_outline, color: kTextColorGrey),
        suffixIcon: IconButton(
          icon: Icon(
            _showPassword ? Icons.visibility_off : Icons.visibility,
            color: kTextColorGrey,
          ),
          onPressed: () {
            setState(() {
              _showPassword = !_showPassword;
            });
          },
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.grey, width: 1.0),
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: kPrimaryRed, width: 2),
        ),
      ),
      validator: (value) {
        if (value == null || value.isEmpty) {
          return 'Please enter your password';
        }
        return null;
      },
    );
  }

  Widget _buildOptions() {
    return Wrap(
      alignment: WrapAlignment.spaceBetween,
      crossAxisAlignment: WrapCrossAlignment.center,
      spacing: 8.0,
      runSpacing: 8.0,
      children: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Checkbox(
              value: _rememberMe,
              onChanged: (value) {
                setState(() {
                  _rememberMe = value ?? false;
                });
              },
              activeColor: kPrimaryRed,
            ),
            const Text('Remember me', style: TextStyle(color: kTextColorGrey)),
          ],
        ),
        TextButton(
          onPressed: () {},
          child: const Text('Forgot password?'),
        ),
      ],
    );
  }

  Widget _buildSignInButton() {
    return ElevatedButton(
      style: ElevatedButton.styleFrom(
        padding: const EdgeInsets.symmetric(vertical: 16),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        backgroundColor: Colors.transparent,
        shadowColor: Colors.transparent,
      ),
      onPressed: () {
        if (_formKey.currentState!.validate()) {
          // Route based on selected role
          switch (_selectedRole) {
            case UserRole.admin:
              Navigator.pushReplacementNamed(context, '/admin');
              break;
            case UserRole.landlord:
              Navigator.pushReplacementNamed(context, '/landlord/dashboard');
              break;
            case UserRole.tenant:
              Navigator.pushReplacementNamed(context, '/tenant/dashboard');
              break;
          }
        }
      },
      child: Ink(
        decoration: BoxDecoration(
          gradient: kPrimaryGradient,
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Center(
          child: Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Text(
              'Sign In',
              style: TextStyle(color: kTextColorWhite, fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDivider() {
    return const Row(
      children: [
        Expanded(child: Divider()),
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 8.0),
          child: Text('Or continue with', style: TextStyle(color: kTextColorGrey)),
        ),
        Expanded(child: Divider()),
      ],
    );
  }

  Widget _buildSocialLogins() {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton.icon(
            icon: const FaIcon(FontAwesomeIcons.google, color: Colors.red),
            label: const Text('Google', style: TextStyle(color: kTextColorBlack)),
            onPressed: () {},
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: OutlinedButton.icon(
            icon: const FaIcon(FontAwesomeIcons.facebook, color: Colors.blue),
            label: const Text('Facebook', style: TextStyle(color: kTextColorBlack)),
            onPressed: () {},
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildSignUpLink() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Text("Don't have an account? ", style: TextStyle(color: kTextColorGrey)),
        TextButton(
          onPressed: () {},
          child: const Text(
            'Sign up for free',
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
        ),
      ],
    );
  }
}
