// Worker E2E Tests — the /worker/* area (dashboard, profile, services, portfolio, bookings)

describe('Worker — Dashboard', () => {
    beforeEach(() => cy.loginAsWorker());

    it('loads the worker dashboard', () => {
        cy.visit('/worker/dashboard');
        cy.contains('Worker Dashboard').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/worker/dashboard');
        cy.url().should('include', '/login');
    });
});

describe('Worker — Profile', () => {
    beforeEach(() => cy.loginAsWorker());

    it('loads the profile edit form for a worker who already has a profile', () => {
        cy.visit('/worker/profile/edit');
        cy.url().should('include', '/worker/profile/edit');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('prefills the existing tagline', () => {
        cy.visit('/worker/profile/edit');
        // v-model sets the DOM property, not the value attribute — assert on the property
        cy.get('input[type=text]')
          .filter((_, el) => (el as HTMLInputElement).value === 'Reliable tradesman for hire')
          .should('have.length', 1);
    });

    it('redirects away from profile/create when a profile already exists', () => {
        cy.visit('/worker/profile/create');
        cy.url().should('not.include', '/worker/profile/create');
    });
});

describe('Worker — Services', () => {
    beforeEach(() => cy.loginAsWorker());

    it('lists the worker services', () => {
        cy.visit('/worker/services');
        cy.contains('My Services').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('shows the seeded service', () => {
        cy.visit('/worker/services');
        cy.contains('General Repairs').should('be.visible');
    });

    it('opens the new-service form', () => {
        cy.visit('/worker/services');
        cy.contains('button', /add service/i).click();
        cy.contains('New Service').should('be.visible');
        cy.get('input, select').should('have.length.greaterThan', 0);
    });
});

describe('Worker — Portfolio', () => {
    beforeEach(() => cy.loginAsWorker());

    it('loads the portfolio page', () => {
        cy.visit('/worker/portfolio');
        cy.contains('Portfolio Photos').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('exposes a file input once the add-photo form is open', () => {
        cy.visit('/worker/portfolio');
        cy.contains('button', /add photo/i).click();
        cy.get('input[type=file]').should('exist');
    });

    it('uploads a portfolio photo', () => {
        cy.visit('/worker/portfolio');
        cy.contains('button', /add photo/i).click();
        cy.get('input[type=file]').selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.contains('button', /^upload$/i).click();
        cy.url().should('include', '/worker/portfolio');
        cy.get('body').should('not.contain', 'Server Error');
        // The uploaded photo is now rendered in the grid
        cy.get('img').should('have.length.greaterThan', 0);
    });

    it('404s for a worker with no profile', () => {
        cy.clearCookies();
        cy.loginAsTenant(); // tenant@ has no worker profile
        cy.request({ url: '/worker/portfolio', failOnStatusCode: false })
          .its('status').should('eq', 404);
    });
});

describe('Worker — Booking Requests', () => {
    beforeEach(() => cy.loginAsWorker());

    it('loads the incoming booking requests', () => {
        cy.visit('/worker/bookings');
        cy.contains('Booking Requests').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('requires auth', () => {
        cy.clearCookies();
        cy.visit('/worker/bookings');
        cy.url().should('include', '/login');
    });
});

describe('Worker — Earnings Report', () => {
    beforeEach(() => cy.loginAsWorker());

    it('loads the worker earnings report', () => {
        cy.visit('/worker/reports');
        cy.contains('Earnings Report').should('be.visible');
        cy.get('body').should('not.contain', 'Server Error');
    });
});
