// Global Cypress support file

// Prevent Inertia.js/Vue uncaught exceptions (e.g. stale component state between tests) from failing tests
Cypress.on('uncaught:exception', () => false);

Cypress.Commands.add('login', (email: string, password: string) => {
    cy.visit('/login');
    cy.get('#email').clear().type(email);
    cy.get('#password').clear().type(password);
    cy.get('button[type=submit]').click();
    cy.url().should('not.include', '/login');
});

Cypress.Commands.add('loginAsAdmin', () => {
    cy.login('admin@rentalapp.com', 'password');
});

Cypress.Commands.add('loginAsLandlord', () => {
    cy.login('landlord@rentalapp.com', 'password');
});

Cypress.Commands.add('loginAsTenant', () => {
    cy.login('tenant@rentalapp.com', 'password');
});

Cypress.Commands.add('loginAsWorker', () => {
    cy.login('worker@rentalapp.com', 'password');
});

declare global {
    namespace Cypress {
        interface Chainable {
            login(email: string, password: string): Chainable<void>;
            loginAsAdmin(): Chainable<void>;
            loginAsLandlord(): Chainable<void>;
            loginAsTenant(): Chainable<void>;
            loginAsWorker(): Chainable<void>;
        }
    }
}
