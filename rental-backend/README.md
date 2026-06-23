# Rental Application — Backend & Frontend

A multi-tenant property rental marketplace built with Laravel 12, Inertia.js, Vue 3 (TypeScript), and Tailwind CSS. Supports three user roles: **Admin**, **Landlord**, and **Tenant**, with a landlord onboarding flow via an **Applicant Landlord** intermediate state.

---

## Project Rules

> **These rules are mandatory. Reference this section before every change.**

1. **No loose code.** Every model, controller, route, button, and component must be accounted for in this README. If it is not documented here, it should not exist in the codebase.
2. **No random changes.** Always present the current state of the code, proposed solutions with pros and cons, and let the user decide before implementing.
3. **Small, precise code.** Write the minimum code needed. Prefer small functions over long methods.
4. **Reusable functions.** Extract shared logic into services (`app/Services/`), composables, or reusable Vue components (`resources/js/Components/`).
5. **Separation of concerns.** Controllers handle HTTP. Services handle business logic. Models handle data. Views handle presentation.
6. **Straightforward, expandable architecture.** New features should slot into existing patterns without restructuring.
7. **Centralized design tokens.** All colors live in `resources/css/app.css` as CSS custom properties and are mapped in `tailwind.config.js`. No hardcoded hex values in components.
8. **Test coverage.** New features require Cypress component tests and/or E2E tests. Backend features require PHPUnit tests.
9. **Properties table constraint.** The `code` column has a CHECK constraint: `CHECK (((code)::text ~ '^PIV[0-9]{4}$'::text))`.
10. **Ask before removing.** Never remove lines of code without explicit user confirmation.
11. **Descriptive naming & production-ready code.** File names, function names, and variable names must be descriptive and self-documenting. Everything written should be production-ready — no placeholder logic, no TODO stubs shipped as final, no abbreviated names that sacrifice clarity.

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend Framework | Laravel | 12.x |
| PHP | PHP | ^8.2 |
| Frontend Bridge | Inertia.js | 2.x |
| Frontend Framework | Vue 3 (Composition API) | 3.4+ |
| Language | TypeScript | 5.6+ |
| Styling | Tailwind CSS | 3.x |
| Build Tool | Vite | 7.x |
| Database | MySQL | — |
| Auth | Laravel Breeze + Sanctum + Socialite (Google OAuth) | — |
| Testing (Frontend) | Cypress (Component + E2E) | 15.x |
| Testing (Backend) | PHPUnit | 11.x |

---

## Getting Started

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment and generate key
cp .env.example .env
php artisan key:generate

# 3. Configure .env (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 4. Run migrations and seed
php artisan migrate
php artisan db:seed

# 5. Install Node dependencies
npm install

# 6. Start development servers (Laravel + Vite concurrently)
composer dev
# Or separately:
php artisan serve   # Port 8000
npm run dev         # Port 5173
```

---

## User Roles & Workflows

### Role Hierarchy

```
Guest → Register → Tenant (default role)
                     ├── Apply as Landlord → Applicant Landlord
                     │                         └── Admin Approves → Landlord
                     └── Browse / Apply for Properties
```

### Tenant Workflow
1. Register/Login (email or Google OAuth).
2. Browse approved property listings on the landing page.
3. View property details, submit reviews, request tours.
4. Apply for a property (submit rental application).
5. If approved by landlord → lease agreement is created.
6. View billing invoices, confirm payments, track ledger.

### Applicant Landlord Workflow
1. Tenant submits a landlord application with documents and selects a verification tier.
2. Application status: `pending` → `under_review` → `approved` / `rejected`.
3. If rejected, can edit and resubmit.
4. If approved, user role is promoted to **Landlord**.

### Landlord Workflow
1. Dashboard shows properties, applications, billing summary.
2. Create and manage property listings (subject to tier limits).
3. Review tenant applications → approve/reject.
4. Create lease agreements for approved tenants.
5. View billing and verify tenant payments.

### Admin Workflow
1. Review and approve/reject landlord applications.
2. Review and approve/reject property listings.
3. Manage users, roles, and permissions (RBAC).
4. View platform statistics and charts.
5. Manage blacklist (fraud prevention).
6. View property reports and audit logs.
7. Manage verification tiers and platform settings.
8. Impersonate users for debugging.

---

## Database Schema

### Core Tables

| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| `users` | Authentication, profiles | `belongsTo` Role |
| `roles` | Role definitions (admin, landlord, tenant, applicant_landlord) | `belongsToMany` Permissions |
| `permissions` | Granular permission definitions | `belongsToMany` Roles |
| `role_permission` | Pivot table | — |
| `properties` | Property listings | `belongsTo` User (landlord), `hasMany` Images/Reviews/Applications |
| `property_images` | Photo paths for listings | `belongsTo` Property |
| `property_applications` | Tenant applications for properties | `belongsTo` Property, User |
| `property_reviews` | Tenant reviews on properties | `belongsTo` Property, User |
| `property_reports` | Fraud/listing reports | `belongsTo` Property, User |

### Landlord Onboarding

| Table | Purpose |
|-------|---------|
| `landlord_applications` | Landlord registration applications with documents |
| `verification_tiers` | Tier definitions (listing limits, pricing) |

### Leasing & Billing

| Table | Purpose |
|-------|---------|
| `lease_agreements` | Finalized leases linking landlord, tenant, property |
| `billing` | Invoices generated against leases |
| `transactions` | Payment receipts against billing |

### Communication & Safety

| Table | Purpose |
|-------|---------|
| `messages` | User-to-user messaging |
| `tour_requests` | Scheduled property viewings |
| `viewing_confirmations` | Verified property tours |
| `landlord_ratings` | Ratings for landlords |
| `blacklists` | Blocked users/emails/IPs |
| `audit_logs` | Admin activity tracking |
| `maintenance_plans` | Property maintenance schedules |
| `saved_properties` | User wishlists |
| `rental_histories` | Tenant rental records |

---

## File Registry

### Backend: Models (`app/Models/`)

| Model | Table | Status |
|-------|-------|--------|
| `User.php` | users | ✅ Active |
| `Role.php` | roles | ✅ Active |
| `Permission.php` | permissions | ✅ Active |
| `Property.php` | properties | ✅ Active |
| `PropertyImage.php` | property_images | ✅ Active |
| `PropertyApplication.php` | property_applications | ✅ Active |
| `PropertyReview.php` | property_reviews | ✅ Active |
| `PropertyReport.php` | property_reports | ✅ Active |
| `LandlordApplication.php` | landlord_applications | ✅ Active |
| `VerificationTier.php` | verification_tiers | ✅ Active |
| `LeaseAgreement.php` | lease_agreements | ✅ Active |
| `Billing.php` | billing | ✅ Active |
| `Transaction.php` | transactions | ✅ Active |
| `Message.php` | messages | ✅ Active |
| `TourRequest.php` | tour_requests | ✅ Active |
| `RentalHistory.php` | rental_histories | ✅ Active |
| `MaintenancePlan.php` | maintenance_plans | ✅ Active (dashboard counts only) |
| `SavedProperty.php` | saved_properties | ✅ Active |
| `AuditLog.php` | audit_logs | ✅ Active |
| `Blacklist.php` | blacklists | ✅ Active |
| `LandlordRating.php` | landlord_ratings | ⚠️ No routes (future API) |
| `ViewingConfirmation.php` | viewing_confirmations | ⚠️ No routes (future API) |

### Backend: Controllers

#### Root (`app/Http/Controllers/`)

| Controller | Routes | Purpose |
|-----------|--------|---------|
| `Controller.php` | — | Base controller |
| `ProfileController.php` | `profile.*` | User profile CRUD |
| `PropertyController.php` | `properties.show` | Public property detail page |
| `PropertyApplicationController.php` | `properties.apply.*` | Tenant applies for property |
| `PropertyReviewController.php` | `properties.reviews.store` | Submit property reviews |
| `TourRequestController.php` | `properties.tour.store` | Submit tour requests |
| `LandlordApplicationController.php` | `landlord.apply.*`, `landlord.status`, `landlord.application.*` | Landlord onboarding |
| `RentalHistoryController.php` | `rental-history.*` | Rental history views |
| `UserController.php` | `users.*`, `admin.users.login-as` | User CRUD + impersonation |
| `ChatController.php` | `chat.*` | Messaging interface |

#### Admin (`app/Http/Controllers/Admin/`)

| Controller | Routes | Purpose |
|-----------|--------|---------|
| `LandlordApplicationAdminController.php` | `admin.applications.*` | Review landlord applications |
| `PropertyApprovalController.php` | `admin.properties.*` | Approve/reject listings |
| `StatisticsController.php` | `admin.statistics.index` | Platform analytics |
| `LandlordProfileController.php` | `admin.landlords.*` | View landlord profiles |
| `RoleController.php` | `admin.roles.*` | RBAC role management |
| `PermissionController.php` | `admin.permissions.*` | RBAC permission management |
| `BlacklistController.php` | `admin.blacklist.*` | Fraud prevention |
| `SettingsController.php` | `admin.settings.*` | Platform settings |
| `AuditLogController.php` | `admin.audit-logs.index` | Activity log viewer |

#### Landlord (`app/Http/Controllers/Landlord/`)

| Controller | Routes | Purpose |
|-----------|--------|---------|
| `DashboardController.php` | `landlord.dashboard` | Landlord dashboard |
| `PropertyController.php` | `landlord.properties.*` | Property CRUD |
| `PropertyApplicationController.php` | `landlord.property-applications.*` | Review tenant applications |
| `LeaseController.php` | `landlord.leases.*` | Lease management |
| `BillingController.php` | `landlord.billing.*` | Billing + payment verification |

#### Tenant (`app/Http/Controllers/Tenant/`)

| Controller | Routes | Purpose |
|-----------|--------|---------|
| `BillingController.php` | `tenant.billing.*` | View/confirm payments |
| `LedgerController.php` | `tenant.ledger.index` | Payment ledger |

#### Auth (`app/Http/Controllers/Auth/`)

| Controller | Purpose |
|-----------|---------|
| `RegisteredUserController.php` | Registration |
| `AuthenticatedSessionController.php` | Login/Logout |
| `GoogleAuthController.php` | Google OAuth |
| `PasswordResetLinkController.php` | Forgot password |
| `NewPasswordController.php` | Reset password |
| `PasswordController.php` | Change password |
| `ConfirmablePasswordController.php` | Confirm password |
| `EmailVerificationNotificationController.php` | Send verification |
| `EmailVerificationPromptController.php` | Verification prompt |
| `VerifyEmailController.php` | Verify email |

#### API (`app/Http/Controllers/Api/`)

| Controller | Routes | Status |
|-----------|--------|--------|
| `PropertyController.php` | `api/properties.*` | ✅ Active |
| `ReviewController.php` | `api/properties/{id}/reviews` | ✅ Active |
| `TourRequestController.php` | `api/tour-requests.*` | ✅ Active |
| `SavedPropertyController.php` | `api/wishlist.*` | ✅ Active |
| `TransactionController.php` | `api/transactions` | ✅ Active |
| `PropertyReportController.php` | `admin.reports.index` (reused) | ✅ Active |
| `ApplicationController.php` | — | ⚠️ No routes (future) |
| `AuthController.php` | — | ⚠️ No routes (future) |
| `LandlordRatingController.php` | — | ⚠️ No routes (future) |
| `MaintenanceController.php` | — | ⚠️ No routes (future) |
| `ViewingConfirmationController.php` | — | ⚠️ No routes (future) |

### Backend: Middleware (`app/Http/Middleware/`)

| Middleware | Purpose |
|-----------|---------|
| `HandleInertiaRequests.php` | Shares auth user, flash data, and ziggy routes with Inertia |
| `CheckRole.php` | Route-level role guard (`role:admin`, `role:landlord`) |
| `CheckPermission.php` | Route-level permission guard |
| `AuditLogMiddleware.php` | Automatic audit logging for requests |

### Backend: Services (`app/Services/`)

| Service | Purpose |
|---------|---------|
| `AuditService.php` | Static helper for creating audit log entries |

### Backend: Mail (`app/Mail/`)

| Mailable | Trigger |
|----------|---------|
| `ApplicationStatusTenant.php` | Tenant notified of application status change |
| `NewApplicationLandlord.php` | Landlord receives new tenant application |
| `LeaseFinalized.php` | Lease agreement finalized notification |
| `PaymentReceivedLandlord.php` | Landlord notified of payment upload |
| `PaymentVerifiedTenant.php` | Tenant notified payment was verified |

### Backend: Seeders (`database/seeders/`)

| Seeder | Purpose |
|--------|---------|
| `DatabaseSeeder.php` | Master seeder |
| `AdminSeeder.php` | Creates admin user |
| `RoleSeeder.php` | Creates roles (admin, landlord, tenant, applicant_landlord) |
| `PermissionSeeder.php` | Creates granular permissions |
| `RolePermissionSeeder.php` | Assigns permissions to roles |
| `VerificationTierSeeder.php` | Creates verification tiers |

---

### Frontend: Layouts (`resources/js/Layouts/`)

| Layout | Used By |
|--------|---------|
| `AuthenticatedLayout.vue` | All authenticated pages |
| `GuestLayout.vue` | Login, Register, Password reset |

### Frontend: Reusable Components (`resources/js/Components/`)

| Component | Purpose |
|-----------|---------|
| **`AppButton.vue`** | Unified button (variants: primary/secondary/danger/success/outline/ghost/link, sizes, loading, Inertia link) |
| **`AppCard.vue`** | Card container (variants: flat/elevated/bordered/glassmorphic, slots: header/default/footer, hover effects) |
| **`AppBadge.vue`** | Status badge pill (variants: success/danger/warning/info/brand/neutral, sizes, shapes) |
| `Modal.vue` | Base modal overlay with transitions |
| `ReportListingModal.vue` | Property fraud report form modal |
| `ChatWarningModal.vue` | Scam detection warning modal |
| `Sidebar.vue` | Navigation sidebar |
| `DashboardCard.vue` | Stat card with icon, value, trend |
| `Toast.vue` | Flash message notification |
| `Pagination.vue` | Paginated list navigation |
| `Dropdown.vue` | Dropdown menu container |
| `DropdownLink.vue` | Link inside dropdown |
| `NavLink.vue` | Navigation link with active state |
| `ResponsiveNavLink.vue` | Mobile navigation link |
| `ApplicationLogo.vue` | SVG logo component |
| `ScamWarningBanner.vue` | Safety warning banner |
| `VerificationBadge.vue` | Landlord verification indicator |
| `PrimaryButton.vue` | Legacy button (use `AppButton` instead) |
| `SecondaryButton.vue` | Legacy button (use `AppButton` instead) |
| `DangerButton.vue` | Legacy button (use `AppButton` instead) |
| `TextInput.vue` | Form text input |
| `InputLabel.vue` | Form label |
| `InputError.vue` | Validation error message |
| `Checkbox.vue` | Form checkbox |

### Frontend: Pages (`resources/js/Pages/`)

| Path | Page | Purpose |
|------|------|---------|
| `Welcome.vue` | Landing | Public property showcase |
| `Dashboard.vue` | Tenant Dashboard | Rental stats, billing summary |
| **Auth/** | | |
| `Auth/Login.vue` | Login | Email + Google OAuth login |
| `Auth/Register.vue` | Register | Email + Google OAuth registration |
| `Auth/ForgotPassword.vue` | Forgot Password | Password reset request |
| `Auth/ResetPassword.vue` | Reset Password | Set new password |
| `Auth/ConfirmPassword.vue` | Confirm Password | Re-authentication |
| `Auth/VerifyEmail.vue` | Verify Email | Email verification prompt |
| **Profile/** | | |
| `Profile/Edit.vue` | Profile | Edit account details |
| **Dashboards/** | | |
| `Dashboards/AdminDashboard.vue` | Admin Dashboard | Admin metrics |
| `Dashboards/LandlordDashboard.vue` | Landlord Dashboard | Landlord metrics |
| `Dashboards/TenantDashboard.vue` | Tenant Dashboard | Tenant metrics |
| **ApplicantLandlord/** | | |
| `ApplicantLandlord/Dashboard.vue` | Onboarding Dashboard | Application status tracker |
| **Landlord/** | | |
| `Landlord/Apply.vue` | Apply as Landlord | Application form |
| `Landlord/EditApplication.vue` | Edit Application | Resubmit rejected application |
| `Landlord/ApplicationStatus.vue` | Application Status | Track application review |
| `Landlord/Benefits.vue` | Benefits | Verification tiers info |
| `Landlord/Dashboard.vue` | Landlord Dashboard | Full landlord dashboard |
| `Landlord/Properties/Index.vue` | My Properties | Property listing management |
| `Landlord/Properties/Create.vue` | Create Property | New property form |
| **Tenant/** | | |
| `Tenant/Ledger/Index.vue` | Payment Ledger | Invoice history |
| **Properties/** | | |
| `Properties/Show.vue` | Property Detail | Public property view |
| `Properties/Apply.vue` | Apply for Property | Tenant application form |
| **RentalHistory/** | | |
| `RentalHistory/Index.vue` | Rental History | Past rentals list |
| `RentalHistory/Show.vue` | Rental Detail | Single rental details |
| **Chat/** | | |
| `Chat/Index.vue` | Messages | Conversation hub |
| **Admin/** | | |
| `Admin/Applications/Index.vue` | Applications List | Review landlord applications |
| `Admin/Applications/Show.vue` | Application Detail | Approve/reject application |
| `Admin/Properties/Index.vue` | Properties List | Review property listings |
| `Admin/Properties/Show.vue` | Property Detail | Approve/reject listing |
| `Admin/Landlords/Index.vue` | Landlords List | View landlord profiles |
| `Admin/Landlords/Show.vue` | Landlord Detail | Landlord profile detail |
| `Admin/Users/Index.vue` | Users List | User management |
| `Admin/Users/Create.vue` | Create User | New user form |
| `Admin/Users/Edit.vue` | Edit User | Edit user details |
| `Admin/Roles/Index.vue` | Roles List | RBAC roles |
| `Admin/Roles/Form.vue` | Role Form | Create/edit role |
| `Admin/Permissions/Index.vue` | Permissions List | RBAC permissions |
| `Admin/Statistics/Index.vue` | Statistics | Platform charts |
| `Admin/Blacklist/Index.vue` | Blacklist | Fraud management |
| `Admin/Reports/Index.vue` | Reports | Property reports |
| `Admin/Settings/Index.vue` | Settings | Platform configuration |

### Frontend: Blade Views (`resources/views/`)

| Path | Purpose |
|------|---------|
| `app.blade.php` | Inertia root template |
| `landlord/leases/print.blade.php` | Printable lease agreement |
| `emails/applications/` | Email templates for application notifications |
| `emails/leases/` | Email templates for lease notifications |
| `emails/payments/` | Email templates for payment notifications |

---

## Design System

### Color Tokens (`resources/css/app.css`)

All colors are defined as CSS custom properties in `:root` and consumed by Tailwind:

| Variable | Value | Tailwind Class |
|----------|-------|---------------|
| `--color-brand-red` | `#E21608` | `bg-brand-red`, `text-brand-red` |
| `--color-brand-orange` | `#FF4520` | `bg-brand-orange`, `text-brand-orange` |
| `--color-success` | `#10b981` | `bg-brand-success`, `text-brand-success` |
| `--color-danger` | `#ef4444` | `bg-brand-danger`, `text-brand-danger` |
| `--color-warning` | `#f59e0b` | `bg-brand-warning`, `text-brand-warning` |
| `--color-info` | `#3b82f6` | `bg-brand-info`, `text-brand-info` |
| `--color-dark-bg` | `#111827` | `bg-dark-bg` |
| `--color-light-bg` | `#1F2937` | `bg-light-bg` |

**Rule:** To change a color globally, update `app.css` only. Never hardcode hex values.

---

## Testing

### Cypress Component Tests ("Stories")

Located alongside components in `resources/js/Components/*.cy.ts`:

| Spec | Component | Tests |
|------|-----------|-------|
| `AppButton.cy.ts` | AppButton.vue | 5 tests (variants, loading, click, link) |
| `AppCard.cy.ts` | AppCard.vue | 5 tests (slots, variants, hover, link) |
| `AppBadge.cy.ts` | AppBadge.vue | 3 tests (variants, shapes, sizes) |

### Cypress E2E Tests

Located in `cypress/e2e/`:

| Spec | Tests |
|------|-------|
| `auth.cy.ts` | 3 tests (landing page, login validation, register structure) |
| `dashboard.cy.ts` | 2 tests (guest redirect guards) |

### PHPUnit Feature Tests

Located in `tests/Feature/`:

| Test | Tests |
|------|-------|
| `PropertyReviewTest.php` | 4 tests (guest block, valid submission, validation, rating range) |
| `ProfileTest.php` | Existing profile tests |
| `Auth/` | Existing auth tests |

### Running Tests

```bash
# Cypress Component Tests (interactive)
npm run test:component:open

# Cypress Component Tests (headless)
npm run test:component

# Cypress E2E Tests (requires php artisan serve)
npm run test:e2e:open    # interactive
npm run test:e2e         # headless

# PHPUnit Backend Tests
php artisan test
```

---

## NPM Scripts

| Script | Command |
|--------|---------|
| `npm run dev` | Start Vite dev server |
| `npm run build` | TypeScript check + production build |
| `npm run test:component` | Cypress component tests (headless) |
| `npm run test:component:open` | Cypress component tests (GUI) |
| `npm run test:e2e` | Cypress E2E tests (headless) |
| `npm run test:e2e:open` | Cypress E2E tests (GUI) |

---

## Known Issues & Future Work

### Unrouted API Controllers (future)
These controllers exist but have no routes registered yet. They are reserved for future API development:
- `Api\ApplicationController.php`
- `Api\AuthController.php`
- `Api\LandlordRatingController.php`
- `Api\MaintenanceController.php`
- `Api\ViewingConfirmationController.php`

### Unrouted Models (future)
- `LandlordRating` — used only by `Api\LandlordRatingController`
- `ViewingConfirmation` — used by `Api\ViewingConfirmationController` and `Property` model relationship

### Debug Route
- `GET /debug-user` exists in `web.php` for development. Remove before production deployment.

### Legacy Buttons
- `PrimaryButton.vue`, `SecondaryButton.vue`, `DangerButton.vue` are legacy. New code should use `AppButton.vue`.

### Missing Factories
- Only `UserFactory.php` exists. `PropertyFactory` and others are needed for comprehensive testing.
