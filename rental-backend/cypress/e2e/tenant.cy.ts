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

describe('Tenant — Schedule a Tour', () => {
    it('"Schedule a Tour" button is visible on a public property show page', () => {
        cy.request({ url: '/api/properties', failOnStatusCode: false }).then(resp => {
            const data = resp.body?.data ?? resp.body ?? [];
            const properties = Array.isArray(data) ? data : [];
            if (properties.length === 0) {
                cy.log('No approved properties in DB — skipping');
                return;
            }
            cy.visit(`/properties/${properties[0].id}`);
            cy.contains('button', /schedule a tour/i).should('be.visible');
        });
    });

    it('clicking the button opens the tour modal with datetime and notes fields', () => {
        cy.loginAsTenant();
        cy.request({ url: '/api/properties', failOnStatusCode: false }).then(resp => {
            const data = resp.body?.data ?? resp.body ?? [];
            const properties = Array.isArray(data) ? data : [];
            if (properties.length === 0) {
                cy.log('No approved properties in DB — skipping');
                return;
            }
            cy.visit(`/properties/${properties[0].id}`);
            cy.contains('button', /schedule a tour/i).click();
            cy.get('input[type=datetime-local]').should('be.visible');
            cy.get('textarea').filter(':visible').should('exist');
            cy.contains('button', /schedule request/i).should('be.visible');
        });
    });

    it('tenant can submit a tour request and the modal closes on success', () => {
        cy.loginAsTenant();
        cy.request({ url: '/api/properties', failOnStatusCode: false }).then(resp => {
            const data = resp.body?.data ?? resp.body ?? [];
            const properties = Array.isArray(data) ? data : [];
            if (properties.length === 0) {
                cy.log('No approved properties in DB — skipping');
                return;
            }
            cy.visit(`/properties/${properties[0].id}`);
            cy.contains('button', /schedule a tour/i).click();

            const future = new Date();
            future.setDate(future.getDate() + 30);
            const pad = (n: number) => String(n).padStart(2, '0');
            const datetimeLocal =
                `${future.getFullYear()}-${pad(future.getMonth() + 1)}-${pad(future.getDate())}T10:00`;

            cy.get('input[type=datetime-local]').type(datetimeLocal);
            cy.get('textarea').filter(':visible').first().type('Interested in a morning visit.');
            cy.contains('button', /schedule request/i).click();
            // On success the modal unmounts — the datetime input disappears
            cy.get('input[type=datetime-local]', { timeout: 10000 }).should('not.exist');
        });
    });

    it('property owner gets an alert and the tour modal does not open', () => {
        cy.loginAsLandlord();
        cy.visit('/landlord/properties');
        cy.get('body').then(($body) => {
            const link = $body.find('a[href*="/landlord/properties/"]')[0];
            if (!link) {
                cy.log('No properties found for landlord — skipping owner tour test');
                return;
            }
            const match = (link.getAttribute('href') ?? '').match(/\/landlord\/properties\/(\d+)/);
            if (!match) {
                cy.log('Could not parse property ID from landlord href — skipping');
                return;
            }
            const id = match[1];
            cy.on('window:alert', (msg) => {
                expect(msg).to.match(/cannot schedule a tour for your own property/i);
            });
            cy.visit(`/properties/${id}`);
            cy.contains('button', /schedule a tour/i).click();
            cy.get('input[type=datetime-local]').should('not.exist');
        });
    });
});

describe('Tenant — Applying for a Property (full flow)', () => {
    beforeEach(() => cy.loginAsTenant());

    it('apply form shows all required fields', () => {
        cy.request({ url: '/api/properties', failOnStatusCode: false }).then(resp => {
            const data = resp.body?.data ?? resp.body ?? [];
            const properties = Array.isArray(data) ? data : [];
            if (properties.length === 0) {
                cy.log('No approved properties in DB — skipping');
                return;
            }
            cy.visit(`/properties/${properties[0].id}/apply`);
            cy.get('body').should('not.contain', 'Server Error');
            cy.get('textarea').first().should('be.visible'); // message to landlord
            cy.get('#adults').should('be.visible');
            cy.get('#children').should('be.visible');
            cy.contains('button', /submit application/i).should('be.visible');
        });
    });

    it('submitting with valid data redirects away from /apply', () => {
        cy.request({ url: '/api/properties', failOnStatusCode: false }).then(resp => {
            const data = resp.body?.data ?? resp.body ?? [];
            const properties = Array.isArray(data) ? data : [];
            if (properties.length === 0) {
                cy.log('No approved properties in DB — skipping');
                return;
            }
            const id = properties[0].id;
            cy.visit(`/properties/${id}/apply`);
            cy.get('textarea').first().type('I am a reliable tenant and would love to rent this property.');
            cy.get('#adults').clear().type('2');
            cy.get('#children').clear().type('0');
            cy.contains('button', /submit application/i).click();
            cy.url({ timeout: 15000 }).should('not.include', '/apply');
            cy.get('body').should('not.contain', 'Server Error');
        });
    });
});

describe('Tenant — Register', () => {
    it('offers a tenant and a landlord card before any form', () => {
        cy.visit('/register');
        cy.get('#name').should('not.exist');
        cy.contains('button', /register as tenant/i).should('be.visible');
        cy.contains('a', /register as landlord/i).should('be.visible');
    });

    it('sends the landlord card to the landlord application', () => {
        cy.visit('/register');
        cy.contains('a', /register as landlord/i).click();
        cy.url().should('include', '/landlord/apply');
    });

    it('keeps the tenant step in the URL and restores the cards on back', () => {
        cy.visit('/register?as=tenant');
        cy.get('#name').should('be.visible');

        cy.visit('/register');
        cy.contains('button', /register as tenant/i).click();
        cy.url().should('include', 'as=tenant');
        cy.go('back');
        cy.get('#name').should('not.exist');
        cy.contains('button', /register as tenant/i).should('be.visible');
    });

    it('shows the registration page', () => {
        cy.visit('/register');
        cy.contains('button', /register as tenant/i).click();
        cy.get('#name').should('be.visible');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
    });

    it('can register a new tenant account', () => {
        const stamp = Date.now();
        cy.visit('/register');
        cy.contains('button', /register as tenant/i).click();
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
