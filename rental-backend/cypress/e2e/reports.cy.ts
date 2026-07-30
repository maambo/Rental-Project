// Reports & Chat E2E Tests

describe('Reports — Admin Financial', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the admin financial reports page', () => {
        cy.visit('/admin/financial-reports');
        cy.contains('Financial Reports').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('exports a CSV', () => {
        cy.request('/admin/financial-reports/export/csv').then(res => {
            expect(res.status).to.eq(200);
            expect(res.headers['content-type']).to.match(/csv|text/i);
        });
    });

    // NOTE: the PDF export is covered by PHPUnit (ReportsTest). It is not asserted
    // here because `php artisan serve` streams the binary body in a way Node's HTTP
    // parser rejects — a dev-server artifact, not an application fault.

    it('blocks a tenant from the admin reports', () => {
        cy.clearCookies();
        cy.loginAsTenant();
        cy.request({ url: '/admin/financial-reports', failOnStatusCode: false })
          .its('status').should('eq', 403);
    });
});

describe('Reports — Admin Moderation Queue', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the reported-properties moderation page', () => {
        cy.visit('/admin/reports');
        cy.contains('Property Reports').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('renders the moderation table columns', () => {
        cy.visit('/admin/reports');
        cy.contains('th', 'Property').should('be.visible');
        cy.contains('th', 'Reasons').should('be.visible');
        cy.contains('th', 'Reporter').should('be.visible');
    });

    it('blocks a tenant from the moderation queue', () => {
        cy.clearCookies();
        cy.loginAsTenant();
        cy.request({ url: '/admin/reports', failOnStatusCode: false })
          .its('status').should('eq', 403);
    });
});

describe('Reports — Landlord Income', () => {
    beforeEach(() => cy.loginAsLandlord());

    it('loads the landlord income report', () => {
        cy.visit('/landlord/reports');
        cy.contains('Income Report').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('exports a CSV', () => {
        cy.request('/landlord/reports/export/csv').its('status').should('eq', 200);
    });

    it('blocks a tenant', () => {
        cy.clearCookies();
        cy.loginAsTenant();
        cy.request({ url: '/landlord/reports', failOnStatusCode: false })
          .its('status').should('eq', 403);
    });
});

describe('Reports — Tenant Payment History', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the tenant payment history', () => {
        cy.visit('/tenant/reports');
        cy.contains('Payment History').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/tenant/reports');
        cy.url().should('include', '/login');
    });
});

describe('Chat', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the messages page', () => {
        cy.visit('/chat');
        cy.contains('Messages').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('opens a conversation with a specific user', () => {
        cy.visit('/chat/1');
        cy.url().should('include', '/chat/');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/chat');
        cy.url().should('include', '/login');
    });
});
