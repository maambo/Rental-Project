// Admin E2E Tests

describe('Admin — Authentication', () => {
    it('redirects unauthenticated users away from admin pages', () => {
        cy.visit('/admin/applications');
        cy.url().should('include', '/login');
    });

    it('logs in as admin and lands on dashboard', () => {
        cy.loginAsAdmin();
        cy.url().should('include', '/dashboard');
    });

    it('shows login page with email and password fields', () => {
        cy.visit('/login');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
        cy.get('button[type=submit]').should('be.visible');
    });

    it('rejects invalid credentials', () => {
        cy.visit('/login');
        cy.get('#email').type('nobody@example.com');
        cy.get('#password').type('wrongpassword');
        cy.get('button[type=submit]').click();
        cy.contains(/credentials|wrong|invalid|These credentials/i).should('be.visible');
    });
});

describe('Admin — Landlord Applications', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('lists landlord applications', () => {
        cy.visit('/admin/applications');
        cy.get('body').should('be.visible');
        cy.url().should('include', '/admin/applications');
    });

    it('page renders without crashing', () => {
        cy.visit('/admin/applications');
        cy.get('body').should('not.contain', 'Server Error');
        cy.get('body').should('not.contain', '500');
    });
});

describe('Admin — Property Approvals', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the properties approval page', () => {
        cy.visit('/admin/properties');
        cy.get('body').should('be.visible');
        cy.url().should('include', '/admin/properties');
    });

    it('page renders without crashing', () => {
        cy.visit('/admin/properties');
        cy.get('body').should('not.contain', 'Server Error');
    });
});

describe('Admin — Utilities Management', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the utilities page', () => {
        cy.visit('/admin/utilities');
        cy.get('body').should('be.visible');
        cy.url().should('include', '/admin/utilities');
    });

    it('shows seeded utility types', () => {
        cy.visit('/admin/utilities');
        // The layout scrolls inside <main>, so assertions must scroll first
        cy.contains('Power').scrollIntoView().should('be.visible');
        cy.contains('Water').scrollIntoView().should('be.visible');
    });

    it('can open create utility type form', () => {
        cy.visit('/admin/utilities');
        cy.contains('button', /add utility type/i).click();
        cy.get('input[placeholder*="Power"], input[placeholder*="e.g"]').should('be.visible');
    });

    it('can create a new utility type', () => {
        cy.visit('/admin/utilities');
        cy.contains('button', /add utility type/i).click();
        const name = `Gas_${Date.now()}`;
        cy.get('input[placeholder*="Power"], input[placeholder*="e.g"]').first().clear().type(name);
        cy.get('button[type=submit]').contains(/create/i).click();
        cy.contains(name).should('be.visible');
    });
});

describe('Admin — Statistics', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the statistics page without crashing', () => {
        cy.visit('/admin/statistics');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('include', '/admin/statistics');
    });
});

describe('Admin — User Management', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('lists all users', () => {
        cy.visit('/admin/users');
        cy.url().should('include', '/admin/users');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('shows the admin user in the list', () => {
        cy.visit('/admin/users?search=admin%40rentalapp.com');
        cy.contains('admin@rentalapp.com').should('be.visible');
    });
});

describe('Admin — Blacklist', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the blacklist page', () => {
        cy.visit('/admin/blacklist');
        cy.url().should('include', '/admin/blacklist');
        cy.get('body').should('not.contain', 'Server Error');
    });
});

describe('Admin — Audit Logs', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the audit logs page', () => {
        cy.visit('/admin/audit-logs');
        cy.url().should('include', '/admin/audit-logs');
        cy.get('body').should('not.contain', 'Server Error');
    });
});

describe('Admin — Settings', () => {
    beforeEach(() => cy.loginAsAdmin());

    it('loads the settings page', () => {
        cy.visit('/admin/settings');
        cy.url().should('include', '/admin/settings');
        cy.get('body').should('not.contain', 'Server Error');
    });
});
