// Landlord E2E Tests

describe('Landlord — Application Form (Public)', () => {
    it('shows the landlord application form', () => {
        cy.visit('/landlord/apply');
        cy.contains('Become a Landlord').should('be.visible');
    });

    it('has all required fields', () => {
        cy.visit('/landlord/apply');
        cy.get('#name').should('exist');
        cy.get('#email').should('exist');
        cy.get('#password').should('exist');
        cy.get('#phone').should('exist');
    });

    it('shows validation errors on empty submit', () => {
        cy.visit('/landlord/apply');
        cy.get('button[type=submit]').click();
        // Inertia returns validation errors — page should still be on apply
        cy.url().should('include', '/landlord/apply');
    });

    it('submits a valid application and redirects to status page', () => {
        const email = `landlord_${Date.now()}@test.com`;
        cy.visit('/landlord/apply');
        cy.get('#name').type('New Test Landlord');
        cy.get('#email').type(email);
        cy.get('#nrc_passport').type(`NRC${Date.now()}`);
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        cy.get('#phone').type('0977123456');
        cy.get('#address').type('Plot 1, Test Road');
        cy.get('#province').type('Lusaka Province');
        cy.get('#town').type('Lusaka');
        cy.get('input[value=private_landlord]').check({ force: true });
        cy.get('input[type=radio][value=nrc]').check({ force: true });
        // id_document, proof_of_address and the (required) selfie
        cy.get('input[type=file]').eq(0).selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('input[type=file]').eq(1).selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('input[type=file]').eq(2).selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/landlord/application-status');
    });
});

describe('Landlord — Application Status', () => {
    beforeEach(() => cy.loginAsLandlord());

    it('loads the landlord application status page', () => {
        cy.visit('/landlord/application-status');
        cy.get('body').should('not.contain', 'Server Error');
    });
});

describe('Landlord — Dashboard', () => {
    beforeEach(() => cy.loginAsLandlord());

    it('loads the landlord dashboard', () => {
        cy.visit('/landlord/dashboard');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('include', '/landlord/dashboard');
    });

    it('shows properties section', () => {
        cy.visit('/landlord/dashboard');
        cy.contains(/properties/i).should('exist');
    });
});

describe('Landlord — Property Management', () => {
    beforeEach(() => cy.loginAsLandlord());

    it('lists landlord properties', () => {
        cy.visit('/landlord/properties');
        cy.url().should('include', '/landlord/properties');
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('shows the Add Property button', () => {
        cy.visit('/landlord/properties');
        cy.contains('a, button', /property/i).should('be.visible');
    });

    it('loads the create property form', () => {
        cy.visit('/landlord/properties/create');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('include', '/landlord/properties/create');
    });

    // The dashboard shell scrolls inside <main>, so anything below the fold is
    // clipped by an overflow-hidden ancestor. Assertions must scroll it into view.
    it('shows property type options', () => {
        cy.visit('/landlord/properties/create');
        cy.contains(/residential/i).scrollIntoView().should('be.visible');
        cy.contains(/commercial/i).scrollIntoView().should('be.visible');
    });

    it('shows listing type options', () => {
        cy.visit('/landlord/properties/create');
        cy.contains(/rent/i).scrollIntoView().should('be.visible');
        cy.contains(/sale/i).scrollIntoView().should('be.visible');
    });

    it('shows province/district/town dropdowns', () => {
        cy.visit('/landlord/properties/create');
        cy.contains('label', /province/i).scrollIntoView().should('be.visible');
        cy.contains('label', /district/i).scrollIntoView().should('be.visible');
        cy.contains('label', /town/i).scrollIntoView().should('be.visible');
    });

    it('shows utilities section with seeded types', () => {
        cy.visit('/landlord/properties/create');
        cy.contains(/utilities/i).scrollIntoView().should('be.visible');
        cy.contains(/power/i).scrollIntoView().should('be.visible');
        cy.contains(/water/i).scrollIntoView().should('be.visible');
    });

    it('shows leaflet map container', () => {
        cy.visit('/landlord/properties/create');
        cy.get('.leaflet-container', { timeout: 10000 }).should('exist');
    });

    it('loads districts when a province is selected', () => {
        cy.visit('/landlord/properties/create');
        // Province select is inside LocationPicker — contains "Province" label text nearby
        cy.contains('label', /province/i).siblings('select, ~ select').first().then($sel => {
            cy.wrap($sel).find('option', { timeout: 10000 }).should('have.length.greaterThan', 1);
            cy.wrap($sel).select(1);
        });
        // After province selected, districts should load
        cy.contains('label', /district/i).siblings('select, ~ select').first()
            .find('option', { timeout: 10000 }).should('have.length.greaterThan', 1);
    });

    it('shows validation errors when submitting empty form', () => {
        cy.visit('/landlord/properties/create');
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/landlord/properties/create');
    });
});

describe('Landlord — Incoming Applications', () => {
    beforeEach(() => cy.loginAsLandlord());

    it('loads the property applications page', () => {
        cy.visit('/landlord/property-applications');
        cy.url().should('include', '/landlord/property-applications');
        cy.get('body').should('not.contain', 'Server Error');
    });
});
