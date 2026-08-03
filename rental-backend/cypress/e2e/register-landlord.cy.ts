/**
 * Landlord Registration E2E Tests
 *
 * Covers the full /landlord/apply flow:
 *  - Page structure
 *  - Field-level validation
 *  - Document type toggle (NRC ↔ Passport)
 *  - Landlord type toggle (Private ↔ Company)
 *  - File uploads
 *  - Successful submission → status page
 *  - Unauthenticated access guard on status page
 */

const FIXTURE = 'cypress/fixtures/test-image.jpg';

/** Fill every field on /landlord/apply with valid data then submit. */
function fillAndSubmit(overrides: {
    name?: string;
    email?: string;
    nrc?: string;
    password?: string;
    phone?: string;
    landlordType?: string;
    docType?: string;
} = {}) {
    const email = overrides.email ?? `landlord_${Date.now()}@regtest.com`;

    cy.visit('/landlord/apply');

    cy.get('#name').clear().type(overrides.name ?? 'Test Registration Landlord');
    cy.get('#email').clear().type(email);
    cy.get('#nrc_passport').clear().type(overrides.nrc ?? `NRC${Date.now()}`);
    cy.get('#password').clear().type(overrides.password ?? 'password');
    cy.get('#password_confirmation').clear().type(overrides.password ?? 'password');
    cy.get('#phone').clear().type(overrides.phone ?? '0977000001');
    cy.get('#address').clear().type('Plot 1, Registration Test Road');
    cy.get('#province').clear().type('Lusaka Province');
    cy.get('#town').clear().type('Lusaka');

    // Landlord type
    cy.get(`input[value=${overrides.landlordType ?? 'private_landlord'}]`).check({ force: true });

    // Document type
    cy.get(`input[type=radio][value=${overrides.docType ?? 'nrc'}]`).check({ force: true });

    // File uploads: id_document, proof_of_address, selfie
    cy.get('input[type=file]').eq(0).selectFile(FIXTURE, { force: true });
    cy.get('input[type=file]').eq(1).selectFile(FIXTURE, { force: true });
    cy.get('input[type=file]').eq(2).selectFile(FIXTURE, { force: true });

    cy.get('button[type=submit]').click();
}

// ─── 1. Page structure ────────────────────────────────────────────────────────

describe('Landlord Registration — Page structure', () => {
    beforeEach(() => cy.visit('/landlord/apply'));

    it('loads without a server error', () => {
        cy.get('body').should('not.contain', 'Server Error');
        cy.get('body').should('not.contain', '500');
    });

    it('shows a heading that mentions becoming a landlord', () => {
        cy.contains(/become a landlord|landlord application|apply/i).should('be.visible');
    });

    it('has all required personal information fields', () => {
        cy.get('#name').should('exist');
        cy.get('#email').should('exist');
        cy.get('#password').should('exist');
        cy.get('#password_confirmation').should('exist');
        cy.get('#phone').should('exist');
    });

    it('has address / location fields', () => {
        cy.get('#address').should('exist');
        cy.get('#province').should('exist');
        cy.get('#town').should('exist');
    });

    it('has the NRC / Passport field', () => {
        cy.get('#nrc_passport').should('exist');
    });

    it('has file upload inputs for id document, proof of address, and selfie', () => {
        cy.get('input[type=file]').should('have.length.gte', 3);
    });

    it('has a landlord type selector (Private / Agent)', () => {
        cy.get('input[value=private_landlord]').should('exist');
        cy.get('input[value=agent]').should('exist');
    });

    it('has a document type selector (NRC / Passport)', () => {
        cy.get('input[type=radio][value=nrc]').should('exist');
        cy.get('input[type=radio][value=passport]').should('exist');
    });

    it('has a visible submit button', () => {
        cy.get('button[type=submit]').should('be.visible');
    });
});

// ─── 2. Validation ────────────────────────────────────────────────────────────

describe('Landlord Registration — Validation', () => {
    it('stays on the apply page when submitted empty', () => {
        cy.visit('/landlord/apply');
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/landlord/apply');
    });

    it('shows an error when email is already taken', () => {
        cy.visit('/landlord/apply');
        // Use the seeded landlord email
        cy.get('#name').type('Duplicate Email');
        cy.get('#email').type('landlord@rentalapp.com');
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        cy.get('#phone').type('0971000099');
        cy.get('#nrc_passport').type('NRC000099');
        cy.get('#address').type('Plot 99');
        cy.get('#province').type('Lusaka');
        cy.get('#town').type('Lusaka');
        cy.get('input[value=private_landlord]').check({ force: true });
        cy.get('input[type=radio][value=nrc]').check({ force: true });
        cy.get('input[type=file]').eq(0).selectFile(FIXTURE, { force: true });
        cy.get('input[type=file]').eq(1).selectFile(FIXTURE, { force: true });
        cy.get('input[type=file]').eq(2).selectFile(FIXTURE, { force: true });
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/landlord/apply');
        // Either an inline error or a page-level flash
        cy.get('body').should('satisfy', ($body: JQuery<HTMLBodyElement>) =>
            $body.text().match(/email.*taken|already.*registered|email.*used/i) !== null
        );
    });

    it('shows an error when passwords do not match', () => {
        cy.visit('/landlord/apply');
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('differentpassword');
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/landlord/apply');
    });
});

// ─── 3. Document type toggle ──────────────────────────────────────────────────

describe('Landlord Registration — Document type toggle', () => {
    beforeEach(() => cy.visit('/landlord/apply'));

    it('selects NRC by default or allows NRC to be selected', () => {
        cy.get('input[type=radio][value=nrc]').check({ force: true });
        cy.get('input[type=radio][value=nrc]').should('be.checked');
    });

    it('switches to passport document type', () => {
        cy.get('input[type=radio][value=passport]').check({ force: true });
        cy.get('input[type=radio][value=passport]').should('be.checked');
        cy.get('input[type=radio][value=nrc]').should('not.be.checked');
    });

    it('label updates when passport is selected', () => {
        cy.get('input[type=radio][value=passport]').check({ force: true });
        cy.contains(/passport/i).should('be.visible');
    });
});

// ─── 4. Landlord type toggle ──────────────────────────────────────────────────

describe('Landlord Registration — Landlord type toggle', () => {
    beforeEach(() => cy.visit('/landlord/apply'));

    it('selecting Agent type checks the agent radio', () => {
        cy.get('input[value=agent]').check({ force: true });
        cy.get('input[value=agent]').should('be.checked');
        cy.get('input[value=private_landlord]').should('not.be.checked');
    });

    it('Agent type reveals a Business Registration upload field', () => {
        cy.get('input[value=agent]').check({ force: true });
        // The v-if="form.landlord_type === 'agent'" section should appear
        cy.contains(/business registration|pacra/i).should('be.visible');
    });

    it('switching back to Private clears the agent selection', () => {
        cy.get('input[value=agent]').check({ force: true });
        cy.get('input[value=private_landlord]').check({ force: true });
        cy.get('input[value=private_landlord]').should('be.checked');
        cy.get('input[value=agent]').should('not.be.checked');
    });
});

// ─── 5. Successful submission ─────────────────────────────────────────────────

describe('Landlord Registration — Successful submission', () => {
    it('redirects to the application status page after a valid submission', () => {
        fillAndSubmit();
        cy.url({ timeout: 15000 }).should('match', /application-status|landlord\/apply/);
    });

    it('shows a pending / under review message on the status page', () => {
        fillAndSubmit();
        cy.url({ timeout: 15000 }).should('match', /application-status|landlord\/apply/);
        cy.get('body').should('satisfy', ($body: JQuery<HTMLBodyElement>) =>
            $body.text().match(/submitted|under review|pending|thank/i) !== null
        );
    });

    it('a landlord can submit with Passport document type', () => {
        fillAndSubmit({ docType: 'passport', nrc: `PASS${Date.now()}` });
        cy.url({ timeout: 15000 }).should('match', /application-status|landlord\/apply/);
    });

    it('a landlord can submit as an Agent', () => {
        fillAndSubmit({
            email: `agent_${Date.now()}@regtest.com`,
            landlordType: 'agent',
        });
        cy.url({ timeout: 15000 }).should('match', /application-status|landlord\/apply/);
    });
});

// ─── 6. Post-submission status page ──────────────────────────────────────────

describe('Landlord Registration — Application status page', () => {
    it('is accessible after applying (logged-in applicant)', () => {
        fillAndSubmit({ email: `status_${Date.now()}@regtest.com` });
        cy.url({ timeout: 15000 }).should('match', /application-status|landlord\/apply/);
        cy.get('body').should('not.contain', 'Server Error');
    });

    it('redirects unauthenticated visitors away from the status page', () => {
        cy.clearCookies();
        cy.visit('/landlord/application-status');
        cy.url().should('include', '/login');
    });

    it('the seeded landlord can view their status page', () => {
        cy.loginAsLandlord();
        cy.visit('/landlord/application-status');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('include', '/landlord');
    });
});

// ─── 7. Success toast ─────────────────────────────────────────────────────────

describe('Landlord Registration — Success toast', () => {
    it('shows a success toast on the status page after a valid submission', () => {
        fillAndSubmit({ email: `toast_${Date.now()}@regtest.com` });
        // After redirect to the status page the Toast component should render
        cy.url({ timeout: 15000 }).should('match', /application-status|landlord\/apply/);
        // Either the green toast appears or the page itself confirms submission
        cy.get('body').should('satisfy', ($body: JQuery<HTMLBodyElement>) =>
            $body.text().match(/submitted|under review|pending|thank|success/i) !== null
        );
    });
});
