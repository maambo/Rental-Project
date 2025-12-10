enum LandlordTier {
  small,
  medium,
  large,
}

extension LandlordTierExtension on LandlordTier {
  String get displayName {
    switch (this) {
      case LandlordTier.small:
        return 'Small Scale';
      case LandlordTier.medium:
        return 'Medium Scale';
      case LandlordTier.large:
        return 'Large Scale';
    }
  }

  int get propertyLimit {
    switch (this) {
      case LandlordTier.small:
        return 3;
      case LandlordTier.medium:
        return 10;
      case LandlordTier.large:
        return -1; // -1 means unlimited
    }
  }

  static LandlordTier fromString(String value) {
    return LandlordTier.values.firstWhere(
      (tier) => tier.toString().split('.').last == value,
      orElse: () => LandlordTier.small,
    );
  }
}
