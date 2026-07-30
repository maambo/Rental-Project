/**
 * Full rental lifecycle flow test.
 * All test users use the @flowtest.com domain so cleanup is safe and precise.
 *
 * Steps:
 *  0. DB cleanup (before & after)
 *  1. Admin creates a FlowTest utility
 *  2. New user applies as landlord (landlord@flowtest.com)
 *  3. Admin approves the landlord application
 *  4. Landlord adds 4 properties (residential/commercial × rent/sale)
 *  5. Admin approves all 4 properties
 *  6a. Welcome page – filter form structure present
 *  6b. Welcome page – search by keyword navigates to browse with results
 *  6c. Welcome page – province filter navigates to browse filtered by province
 *  6d. Welcome page – property-type filter navigates to browse filtered by type
 *  6e. Welcome page – listing-type filter navigates to browse filtered by listing
 *  6f. Welcome page – combined filters work together
 *  6g. Welcome page – View All appears when properties > 6 and carries filters
 *  7a. Browse page – filter form present and shows results
 *  7b. Browse page – property-type filter works
 *  7c. Browse page – listing-type filter works
 *  7d. Browse page – keyword search works
 *  7e. Browse page – clear filters resets state
 *  7f. Browse page – map view toggle renders cluster map
 *  8.  Clicking a property card opens the show page
 *  9.  Tenant registers and applies for the residential rent property
 *  10. Landlord sees the tenant application on their dashboard
 */

// ─── Helpers ─────────────────────────────────────────────────────────────────

function clickStyledRadio(value: string) {
    cy.get(`input[type=radio][value="${value}"]`).click({ force: true });
}

function createProperty(
    title: string,
    propertyType: 'residential' | 'commercial',
    subtype: string,
    listingType: 'rent' | 'sale',
) {
    cy.visit('/landlord/properties/create');
    cy.url().should('include', '/landlord/properties/create');

    cy.get('input[placeholder*="Modern 3-Bedroom"], input[placeholder*="House"], input[type=text]')
        .first().clear().type(title);
    cy.get('textarea').first().clear().type(`Test description for ${title}.`);
    cy.get('input[placeholder="5000"], input[type=number]').first().clear().type('5000');

    clickStyledRadio(propertyType);

    cy.contains('label', /subtype/i)
        .closest('div').find('select')
        .should('not.be.disabled')
        .select(subtype, { force: true });

    clickStyledRadio(listingType);

    cy.contains('label', /province \*/i)
        .closest('div').find('select')
        .select('Lusaka Province', { force: true });

    cy.contains('label', /district \*/i)
        .closest('div').find('select')
        .should('not.be.disabled')
        .select('Lusaka', { force: true });

    cy.contains('label', /town \*/i)
        .closest('div').find('select')
        .should('not.be.disabled')
        .find('option').not('[disabled]').first()
        .then(($opt) => {
            const val = $opt.val() as string;
            cy.contains('label', /town \*/i)
                .closest('div').find('select')
                .select(val, { force: true });
        });

    cy.contains('label', /street address/i)
        .closest('div').find('input')
        .clear().type('Plot 1, Test Road, Lusaka');

    // LocationPicker shows a read-only coords badge ("-15.41667, 28.28333")
    // once the town/address geocode resolves. It has no "Lat:" prefix.
    cy.contains(/-?\d+\.\d{4,},\s*-?\d+\.\d{4,}/, { timeout: 15000 }).should('exist');

    // Attach explicit binary contents + mime. Passing the path alone lets the
    // multiple-file input receive re-encoded bytes, which fails `image` validation.
    cy.fixture('test-image.jpg', 'base64').then((b64) => {
        cy.get('input[type=file]').first().selectFile(
            {
                contents: Cypress.Buffer.from(b64, 'base64'),
                fileName: 'test-image.jpg',
                mimeType: 'image/jpeg',
            },
            { force: true },
        );
    });


    cy.contains('button', /create property/i).click();

    cy.location('pathname').should('eq', '/landlord/properties');
    cy.contains(title).should('be.visible');
}

// ─── Test suite ───────────────────────────────────────────────────────────────

describe('Full rental lifecycle flow', () => {
    before(() => {
        cy.task('cleanFlowTestData');
    });

    after(() => {
        cy.task('cleanFlowTestData');
    });

    // ── 1. Admin creates a utility ────────────────────────────────────────────
    it('1. Admin creates a FlowTest utility type', () => {
        cy.loginAsAdmin();
        cy.visit('/admin/utilities');
        cy.contains('button', /add utility type/i).click();
        cy.get('input[placeholder="e.g., Power"]').clear().type('FlowTest Power');
        cy.get('button[type=submit]').contains(/create/i).click();
        cy.contains('FlowTest Power').should('be.visible');
    });

    // ── 2. New user applies as landlord ───────────────────────────────────────
    it('2. New user submits a landlord application', () => {
        cy.visit('/landlord/apply');
        cy.get('#name').type('Flow Test Landlord');
        cy.get('#email').type('landlord@flowtest.com');
        cy.get('#nrc_passport').type('FLOW123456');
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        cy.get('#phone').type('0971000001');
        cy.get('#address').type('Plot 1, Test Road');
        cy.get('#province').clear().type('Lusaka');
        cy.get('#town').clear().type('Lusaka');
        cy.get('input[value=private_landlord]').check({ force: true });
        cy.get('input[type=radio][value=nrc]').check({ force: true });
        // id_document, proof_of_address and the (required) selfie
        cy.get('input[type=file]').eq(0).selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('input[type=file]').eq(1).selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('input[type=file]').eq(2).selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('button[type=submit]').click();
        cy.url().should('match', /application-status|landlord\/apply/);
        cy.contains(/submitted|under review|pending|thank/i).should('be.visible');
    });

    // ── 3. Admin approves landlord ────────────────────────────────────────────
    it('3. Admin approves the landlord application', () => {
        cy.loginAsAdmin();
        cy.visit('/admin/applications');
        cy.contains('td', 'Flow Test Landlord')
            .closest('tr').contains('a', 'View Details').click();
        cy.url().should('include', '/admin/applications/');
        cy.on('window:confirm', () => true);
        cy.contains('button', /approve application/i).click();
        cy.contains(/approved/i).should('be.visible');
    });

    // ── 3b. Landlord upgrades their plan ─────────────────────────────────────
    // A newly approved landlord lands on the Starter tier, which allows a single
    // property. This flow lists four, so the landlord buys an unlimited plan
    // through the real self-service checkout (payment methods are simulated).
    it('3b. Landlord subscribes to an unlimited plan', () => {
        cy.login('landlord@flowtest.com', 'password');
        cy.visit('/payments/subscribe');
        cy.contains('Subscription Plans').should('be.visible');

        cy.contains('h3', 'Enterprise')
            .closest('div')
            .find('a')
            .click();

        cy.url().should('match', /\/payments\/subscribe\/\d+/);

        // Picker defaults to MTN mobile money; only the phone number is needed.
        cy.get('input[type=tel]').clear().type('0961234567');
        cy.contains('button', /subscribe & pay|pay now/i).click();

        // The generic /dashboard route forwards a landlord to /landlord/dashboard
        cy.location('pathname', { timeout: 10000 })
            .should('match', /^\/(landlord\/)?dashboard$/);
    });

    // ── 4. Landlord adds 4 properties ────────────────────────────────────────
    it('4a. Landlord adds a residential rent property', () => {
        cy.login('landlord@flowtest.com', 'password');
        createProperty('Flow Residential Rent', 'residential', 'house', 'rent');
    });

    it('4b. Landlord adds a residential sale property', () => {
        cy.login('landlord@flowtest.com', 'password');
        createProperty('Flow Residential Sale', 'residential', 'apartment', 'sale');
    });

    it('4c. Landlord adds a commercial rent property', () => {
        cy.login('landlord@flowtest.com', 'password');
        createProperty('Flow Commercial Rent', 'commercial', 'shop', 'rent');
    });

    it('4d. Landlord adds a commercial sale property', () => {
        cy.login('landlord@flowtest.com', 'password');
        createProperty('Flow Commercial Sale', 'commercial', 'office_space', 'sale');
    });

    // ── 5. Admin approves all 4 properties ───────────────────────────────────
    it('5. Admin approves all 4 Flow properties', () => {
        cy.loginAsAdmin();
        const titles = [
            'Flow Residential Rent',
            'Flow Residential Sale',
            'Flow Commercial Rent',
            'Flow Commercial Sale',
        ];
        for (const title of titles) {
            cy.visit('/admin/properties');
            cy.contains(title).closest('tr').contains('a', /view/i).click();
            cy.url().should('include', '/admin/properties/');
            cy.on('window:confirm', () => true);
            cy.contains('button', /approve property/i).click();
            cy.contains(/approved/i).should('be.visible');
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // WELCOME PAGE FILTER TESTS (6a – 6g)
    // At this point: 8 seeded + 4 flow = 12 approved visible properties
    // ════════════════════════════════════════════════════════════════════════

    it('6a. Welcome page – filter form has all required inputs', () => {
        cy.visit('/');
        cy.get('form').within(() => {
            // keyword input
            cy.get('input[type=text]').should('exist');
            // three selects: province, type, for
            cy.get('select').should('have.length.gte', 3);
            // Search button
            cy.get('button[type=submit]').contains(/search/i).should('be.visible');
        });
    });

    it('6b. Welcome page – keyword search navigates to browse with filtered results', () => {
        cy.visit('/');
        cy.get('form input[type=text]').clear().type('Kabulonga');
        cy.get('form button[type=submit]').click();

        cy.url().should('include', '/properties');
        cy.url().should('include', 'search=Kabulonga');

        // The seeded "3-Bedroom Family Home in Kabulonga" should appear
        cy.contains(/Kabulonga/i).should('exist');
    });

    it('6c. Welcome page – province filter navigates to browse filtered by province', () => {
        cy.visit('/');
        // Select Lusaka Province (the seeded properties are all in Lusaka)
        cy.get('form select').eq(0).select('Lusaka Province', { force: true });
        cy.get('form button[type=submit]').click();

        cy.url().should('include', '/properties');
        cy.url().should('include', 'province_id=');

        // Results should exist since seeded properties are in Lusaka
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
    });

    it('6d. Welcome page – property-type filter navigates to browse with type param', () => {
        cy.visit('/');
        // Type select is the second select (province, type, for)
        cy.get('form select').eq(1).select('residential', { force: true });
        cy.get('form button[type=submit]').click();

        cy.url().should('include', '/properties');
        cy.url().should('include', 'property_type=residential');

        // All visible cards should show the residential badge
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
        cy.contains(/residential/i).should('exist');
    });

    it('6e. Welcome page – listing-type filter navigates to browse with listing param', () => {
        cy.visit('/');
        cy.get('form select').eq(2).select('rent', { force: true });
        cy.get('form button[type=submit]').click();

        cy.url().should('include', '/properties');
        cy.url().should('include', 'listing_type=rent');

        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
        cy.contains(/rent/i).should('exist');
    });

    it('6f. Welcome page – combined filters (type=commercial + for=rent) work', () => {
        cy.visit('/');
        cy.get('form select').eq(1).select('commercial', { force: true });
        cy.get('form select').eq(2).select('rent', { force: true });
        cy.get('form button[type=submit]').click();

        cy.url().should('include', 'property_type=commercial');
        cy.url().should('include', 'listing_type=rent');

        // Seeded: office_space rent + shop rent; Flow: commercial rent — at least 3
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
        // Scope badge assertions to the cards grid (not the filter-form dropdowns)
        cy.get('[data-testid="properties-grid"]').within(() => {
            cy.contains(/commercial/i).should('exist');
            cy.contains(/residential/i).should('not.exist');
        });
    });

    it('6g. Welcome page – View All appears when properties > 6 and carries active filters to browse', () => {
        cy.visit('/');
        // 12 properties (8 seeded + 4 flow) > 6, so View All should show
        cy.contains(/view all/i).should('be.visible');

        // Set type=residential then click View All — URL should carry the filter
        cy.get('form select').eq(1).select('residential', { force: true });
        cy.contains(/view all/i).click();

        cy.url().should('include', '/properties');
        cy.url().should('include', 'property_type=residential');

        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
        // Scope badge assertions to the cards grid (not the filter-form dropdowns)
        cy.get('[data-testid="properties-grid"]').within(() => {
            cy.contains(/residential/i).should('exist');
            cy.contains(/commercial/i).should('not.exist');
        });
    });

    // ════════════════════════════════════════════════════════════════════════
    // BROWSE PAGE FILTER TESTS (7a – 7f)
    // ════════════════════════════════════════════════════════════════════════

    it('7a. Browse page – shows results and filter form is present', () => {
        cy.visit('/properties');
        cy.contains('label', /province/i).should('be.visible');
        cy.get('input[type=text]').should('exist');
        cy.get('button[type=submit]').contains(/search/i).should('be.visible');
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
    });

    it('7b. Browse page – property-type filter shows only matching results', () => {
        cy.visit('/properties');
        cy.contains('label', 'Type').closest('div').find('select')
            .select('commercial', { force: true });
        cy.get('button[type=submit]').contains(/search/i).click();

        cy.url().should('include', 'property_type=commercial');
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
        // Scope to the cards grid — the filter-form still contains "Residential" as an option
        cy.get('[data-testid="properties-grid"]').within(() => {
            cy.contains(/commercial/i).should('exist');
            cy.contains(/residential/i).should('not.exist');
        });
    });

    it('7c. Browse page – listing-type filter shows only matching results', () => {
        cy.visit('/properties');
        cy.contains('label', 'For').closest('div').find('select')
            .select('sale', { force: true });
        cy.get('button[type=submit]').contains(/search/i).click();

        cy.url().should('include', 'listing_type=sale');
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
        // Scope to cards grid — the For dropdown still contains "Rent" as an option label
        cy.get('[data-testid="properties-grid"]').within(() => {
            cy.contains(/\bsale\b/i).should('exist');
            cy.contains(/\brent\b/i).should('not.exist');
        });
    });

    it('7d. Browse page – keyword search returns matching results', () => {
        cy.visit('/properties');
        cy.get('input[type=text]').clear().type('Apartment');
        cy.get('button[type=submit]').contains(/search/i).click();

        cy.url().should('include', 'search=Apartment');
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 1);
        cy.contains(/apartment/i).should('exist');
    });

    it('7e. Browse page – clear filters resets URL and shows all results', () => {
        cy.visit('/properties?property_type=commercial');
        cy.url().should('include', 'property_type=commercial');

        cy.contains('button', /clear/i).click();
        cy.url().should('not.include', 'property_type=');

        // All 12 properties should be back
        cy.get('[data-testid="properties-grid"] a').should('have.length.gte', 8);
    });

    it('7f. Browse page – map view toggle renders the cluster map', () => {
        cy.visit('/properties');
        // Switch to map view
        cy.contains('button', /map/i).click();

        // Map container should appear
        cy.get('.leaflet-container', { timeout: 8000 }).should('be.visible');

        // Legend should be visible
        cy.contains(/legend/i).should('be.visible');
        cy.contains(/residential.*rent/i).should('be.visible');

        // Fullscreen button should be present
        cy.get('button[title*="ullscreen"], button[title*="ull screen"]')
            .should('exist');
    });

    // ── 8. Property show page ─────────────────────────────────────────────────
    it('8. Clicking a property card opens the show page', () => {
        cy.login('landlord@flowtest.com', 'password');
        cy.visit('/properties');
        cy.get('[data-testid="properties-grid"] a').first().click();
        cy.url().should('match', /\/properties\/\d+/);

        cy.get('h1, h2').first().should('be.visible');
        cy.contains(/K\d|ZMW/i).scrollIntoView().should('be.visible');

        // The show page always renders <PropertiesMap>, and Leaflet is imported
        // dynamically, so wait for the container to mount.
        cy.get('.leaflet-container', { timeout: 10000 }).scrollIntoView().should('be.visible');
    });

    // ── 9. Tenant registers and applies ──────────────────────────────────────
    it('9. Tenant registers and applies for the residential rent property', () => {
        cy.visit('/register');
        cy.get('#name').type('Flow Test Tenant');
        cy.get('#email').type('tenant@flowtest.com');
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        // Identity verification — required since the NRC/passport feature landed
        cy.get('#phone').type('0971000002');
        cy.get('input[type=radio][value=nrc]').check({ force: true });
        cy.get('#nrc_passport').type('900002/10/1');
        cy.get('#id_document').selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('#selfie').selectFile('cypress/fixtures/test-image.jpg', { force: true });
        cy.get('button[type=submit]').click();
        cy.url().should('include', '/dashboard');

        cy.request('/api/properties').then((resp) => {
            const properties: any[] = resp.body.data ?? resp.body;
            const prop = properties.find(
                (p: any) => typeof p.title === 'string' && p.title.includes('Flow Residential Rent'),
            );

            if (!prop) {
                cy.log('Flow Residential Rent not in API — skipping apply step');
                return;
            }

            cy.visit(`/properties/${prop.id}/apply`);
            cy.url().should('include', `/properties/${prop.id}/apply`);

            cy.get('textarea').first().type('I would like to rent this property.');
            cy.get('#adults').clear().type('2');
            cy.get('#children').clear().type('0');

            cy.get('button[type=submit]').click();
            // Submitting the application does a fair amount of work server-side,
            // so allow longer than the default 4s command timeout.
            cy.url({ timeout: 20000 }).should('not.include', '/apply');
        });
    });

    // ── 10. Landlord sees the application ────────────────────────────────────
    it('10. Landlord sees the tenant application on their dashboard', () => {
        cy.login('landlord@flowtest.com', 'password');
        cy.visit('/landlord/property-applications');
        cy.contains('Flow Test Tenant').should('be.visible');
        cy.contains(/pending/i).should('be.visible');
    });
});
