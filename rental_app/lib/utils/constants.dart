import 'package:flutter/material.dart';

// Primary Colors from the new design
const Color kPrimaryRed = Color(0xFFE21608);
const Color kPrimaryOrange = Color(0xFFFF4520);

// Background Colors
const Color kBackgroundColorDark = Color(0xFF111827);
const Color kBackgroundColorLight = Color(0xFF1F2937);

// Text & UI Colors
const Color kTextColorWhite = Colors.white;
const Color kTextColorGrey = Colors.grey;
const Color kTextColorBlack = Colors.black87;

// Gradient for buttons and headers
const LinearGradient kPrimaryGradient = LinearGradient(
  colors: [kPrimaryRed, kPrimaryOrange],
  begin: Alignment.centerLeft,
  end: Alignment.centerRight,
);

// Background Gradient
const LinearGradient kBackgroundGradient = LinearGradient(
  colors: [kBackgroundColorLight, kBackgroundColorDark],
  begin: Alignment.topCenter,
  end: Alignment.bottomCenter,
);