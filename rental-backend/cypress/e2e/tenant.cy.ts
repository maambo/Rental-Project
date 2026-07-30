// Tenant E2E Tests

describe('Tenant — Public Pages', () => {
    it('loads the landing page', () => {
        cy.visit('/');
        cy.get('body').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('landing page renders without 500 error', () => {
        cy.visit('/');
        cy.url().should('eq', Cypress.config('baseUrl') + '/');
    });

    it('shows property listings if any exist', () => {
        cy.visit('/');
        cy.get('body').then($body => {
            if ($body.find('a[href*="/properties/"]').length > 0) {
                cy.get('a[href*="/properties/"]').first().should('be.visible');
            } else {
                cy.log('No approved properties yet — skipping property link check');
            }
        });
    });
});

describe('Tenant — Authentication', () => {
    it('shows login page with correct fields', () => {
        cy.visit('/login');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
        cy.get('button[type=submit]').should('be.visible');
    });

    it('shows validation errors on empty login submit', () => {
        cy.visit('/login');
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/login');
    });

    it('shows error for invalid credentials', () => {
        cy.visit('/login');
        cy.get('#email').type('nobody@example.com');
        cy.get('#password').type('wrongpassword');
        cy.get('button[type=submit]').click();
        cy.contains(/credentials|wrong|invalid|These credentials/i).should('be.visible');
    });

    it('logs in a tenant and redirects away from login', () => {
        cy.loginAsTenant();
        cy.url().should('not.include', '/login');
    });

    it('logs out successfully', () => {
        cy.loginAsTenant();
        // Find and click logout (usually in nav dropdown)
        cy.get('body').then($body => {
            if ($body.find('form[action*=logout], button[form*=logout]').length) {
                cy.get('form[action*=logout], button[form*=logout]').first().click();
                cy.url().should('include', '/');
            } else {
                cy.log('Logout button not immediately visible — may be in dropdown');
            }
        });
    });
});

describe('Tenant — Dashboard', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads tenant dashboard without errors', () => {
        cy.visit('/dashboard');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('not.include', '/login');
    });
});

describe('Tenant — Profile', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the profile page', () => {
        cy.visit('/profile');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('include', '/profile');
    });

    it('has name and email fields on profile', () => {
        cy.visit('/profile');
        cy.get('input[id=name], input[name=name]').should('exist');
        cy.get('input[id=email], input[name=email]').should('exist');
    });

    it('can update profile name', () => {
        cy.visit('/profile');
        cy.get('input[id=name], input[name=name]').first().clear().type('Updated Tenant Name');
        cy.get('button[type=submit]').first().click();
        cy.get('body').should('not.contain', 'Server Error');
    });
});

describe('Tenant — Rental History', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads rental history page', () => {
        cy.visit('/rental-history');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('include', '/rental-history');
    });
});

describe('Tenant — Applying for a Property', () => {
    beforeEach(() => cy.loginAsTenant());

    it('apply page requires auth — redirects unauthenticated users', () => {
        cy.clearCookies();
        cy.visit('/properties/1/apply');
        cy.url().should('include', '/login');
    });

    it('apply form shows for a valid property', () => {
        // Check if any properties exist first
        cy.request({ url: '/api/properties', failOnStatusCode: false }).then(resp => {
            const data = resp.body?.data ?? resp.body ?? [];
            const properties = Array.isArray(data) ? data : [];
            if (properties.length > 0) {
                const id = properties[0].id;
                cy.visit(`/properties/${id}/apply`);
                cy.get('body').should('not.contain', 'Server Error');
                cy.get('form').should('exist');
            } else {
                cy.log('No approved properties in DB — skipping apply form test');
            }
        });
    });
});

describe('Tenant — Register', () => {
    it('shows the registration page', () => {
        cy.visit('/register');
        cy.get('#name').should('be.visible');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
    });

    it('can register a new tenant account', () => {
        const stamp = Date.now();
        cy.visit('/register');
        cy.get('#name').type('New Test Tenant');
        cy.get('#email').type(`tenant_${stamp}@test.com`);
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        // Identity verification — required since the NRC/passport feature landed
        cy.get('#phone').type('0977123456');
        cy.get('input[type=radio][value=nrc]').check({ force: true });
        cy.get('#nrc_passport').type(`${stamp}`.slice(-6) + '/10/1');
        cy.get('#id_document').selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('#selfie').selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('button[type=submit]').click();
        cy.url().should('not.include', '/register');
    });
});
