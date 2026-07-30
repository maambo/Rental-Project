// Payments E2E Tests — subscription checkout, tenant billing/ledger, landlord billing

describe('Payments — Subscription Plans', () => {
    beforeEach(() => cy.loginAsLandlord());

    it('loads the subscription plans page', () => {
        cy.visit('/payments/subscribe');
        cy.contains('Subscription Plans').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('lists the seeded tiers', () => {
        cy.visit('/payments/subscribe');
        cy.contains(/starter/i).should('be.visible');
    });

    it('opens the checkout page for a tier', () => {
        cy.visit('/payments/subscribe');
        cy.get('a[href*="/payments/subscribe/"]').first().then($a => {
            cy.visit($a.attr('href') as string);
        });
        cy.url().should('match', /\/payments\/subscribe\/\d+/);
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('shows the payment method picker on checkout', () => {
        cy.visit('/payments/subscribe');
        cy.get('a[href*="/payments/subscribe/"]').first().then($a => {
            cy.visit($a.attr('href') as string);
        });
        cy.contains(/mobile money|card/i).should('be.visible');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/payments/subscribe');
        cy.url().should('include', '/login');
    });
});

describe('Payments — Tenant Billing', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the tenant billing page', () => {
        cy.visit('/tenant/billing');
        cy.contains('My Billing').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/tenant/billing');
        cy.url().should('include', '/login');
    });

    it('blocks a landlord from the tenant billing page', () => {
        cy.clearCookies();
        cy.loginAsLandlord();
        cy.request({ url: '/tenant/billing', failOnStatusCode: false })
          .its('status').should('eq', 403);
    });
});

describe('Payments — Tenant Ledger', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the tenant ledger', () => {
        cy.visit('/tenant/ledger');
        cy.url().should('include', '/tenant/ledger');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/tenant/ledger');
        cy.url().should('include', '/login');
    });
});

describe('Payments — Landlord Billing', () => {
    beforeEach(() => cy.loginAsLandlord());

    it('loads the landlord tenant-billing page', () => {
        cy.visit('/landlord/billing');
        cy.contains('Tenant Billing').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('blocks a tenant from the landlord billing page', () => {
        cy.clearCookies();
        cy.loginAsTenant();
        cy.request({ url: '/landlord/billing', failOnStatusCode: false })
          .its('status').should('eq', 403);
    });
});
