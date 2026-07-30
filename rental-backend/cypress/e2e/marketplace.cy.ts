// Marketplace E2E Tests — public worker directory + client-side bookings

describe('Marketplace — Worker Directory (Public)', () => {
    it('loads the workers marketplace', () => {
        cy.visit('/workers');
        cy.contains('Skilled Workers Marketplace').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('shows the seeded verified worker', () => {
        cy.visit('/workers');
        cy.contains('Test Worker').should('be.visible');
    });

    it('shows trade category filters', () => {
        cy.visit('/workers');
        cy.get('select, [role=combobox]').should('have.length.greaterThan', 0);
    });

    it('filters by search term without erroring', () => {
        cy.visit('/workers?search=Test');
        cy.url().should('include', 'search=Test');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('returns an empty state for a search that matches nothing', () => {
        cy.visit('/workers?search=zzzznotarealworker');
        cy.get('body').should('not.contain', 'Server Error');
        cy.contains('Test Worker').should('not.exist');
    });
});

describe('Marketplace — Worker Detail', () => {
    // Resolve the worker id from the listing so the spec never hardcodes an id
    const openFirstWorker = () => {
        cy.visit('/workers');
        cy.contains('Test Worker').closest('a').then($a => {
            const href = $a.attr('href');
            if (href) cy.visit(href);
        });
    };

    it('opens the worker profile page', () => {
        openFirstWorker();
        cy.url().should('match', /\/workers\/\d+/);
        cy.contains('About').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('lists the worker services', () => {
        openFirstWorker();
        // Services live behind the "services" tab
        cy.contains('button', /^services/i).click();
        cy.contains('General Repairs').should('be.visible');
    });

    it('404s for a worker id that does not exist', () => {
        cy.request({ url: '/workers/999999', failOnStatusCode: false })
          .its('status').should('eq', 404);
    });
});

describe('Marketplace — Booking a Worker', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the booking form for a worker', () => {
        cy.visit('/workers');
        cy.contains('Test Worker').closest('a').then($a => {
            const href = $a.attr('href');
            if (href) cy.visit(`${href}/book`);
        });
        cy.url().should('include', '/book');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('keeps the user on the form when required fields are empty', () => {
        cy.visit('/workers');
        cy.contains('Test Worker').closest('a').then($a => {
            const href = $a.attr('href');
            if (href) cy.visit(`${href}/book`);
        });
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/book');
    });

    it('requires auth to reach the booking form', () => {
        cy.clearCookies();
        cy.visit('/workers');
        cy.contains('Test Worker').closest('a').then($a => {
            const href = $a.attr('href');
            if (href) cy.visit(`${href}/book`);
        });
        cy.url().should('include', '/login');
    });
});

describe('Marketplace — My Bookings (Client)', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the client bookings list', () => {
        cy.visit('/marketplace/bookings');
        cy.contains('My Bookings').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/marketplace/bookings');
        cy.url().should('include', '/login');
    });
});
