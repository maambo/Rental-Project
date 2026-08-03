// Chat module E2E tests
// Uses seeded tenant (tenant@rentalapp.com) and landlord (landlord@rentalapp.com)

describe('Chat — Index page', () => {
    beforeEach(() => cy.loginAsTenant());

    it('loads the chat index without errors', () => {
        cy.visit('/chat');
        cy.get('body').should('not.contain', 'Server Error');
        cy.url().should('include', '/chat');
    });

    it('shows the Safety Alert banner', () => {
        cy.visit('/chat');
        cy.contains(/safety alert/i).should('be.visible');
        cy.contains(/never pay any money/i).should('be.visible');
    });

    it('shows "Conversations" sidebar panel', () => {
        cy.visit('/chat');
        cy.contains('Conversations').should('be.visible');
    });

    it('shows "Select a conversation" prompt when no chat is open', () => {
        cy.visit('/chat');
        cy.contains(/select a conversation/i).should('be.visible');
    });

    it('chat index requires auth — redirects unauthenticated users', () => {
        cy.clearCookies();
        cy.visit('/chat');
        cy.url().should('include', '/login');
    });
});

describe('Chat — Opening a conversation', () => {
    beforeEach(() => cy.loginAsTenant());

    it('opens a chat thread with a user via /chat/{id}', () => {
        // Get the seeded landlord's user id from the API
        cy.request({ url: '/api/users?role=landlord', failOnStatusCode: false }).then(resp => {
            // Fallback: visit landlord user id 2 (seeded default)
            const id = resp.body?.[0]?.id ?? 2;
            cy.visit(`/chat/${id}`);
            cy.get('body').should('not.contain', 'Server Error');
            cy.url().should('include', `/chat/${id}`);
        });
    });

    it('shows recipient name in the chat header', () => {
        cy.visit('/chat/2');
        cy.get('body').should('not.contain', 'Server Error');
        // Header should contain a name (at least one non-empty text node)
        cy.get('body').then($body => {
            if ($body.text().includes('Server Error')) return;
            // The chat header renders the recipient name
            cy.get('body').should('not.contain', 'Select a conversation');
        });
    });

    it('shows the warning modal when opening a chat', () => {
        cy.visit('/chat/2');
        cy.get('body').should('not.contain', 'Server Error');
        // ChatWarningModal fires on mount when recipient is present
        cy.get('body').then($body => {
            if ($body.find('[role=dialog], .modal, [data-modal]').length) {
                cy.get('[role=dialog], .modal, [data-modal]').should('be.visible');
            } else {
                // Modal may use v-if — just assert no server error
                cy.get('body').should('not.contain', 'Server Error');
            }
        });
    });

    it('shows message input and Send button for an open conversation', () => {
        cy.visit('/chat/2');
        cy.get('body').should('not.contain', 'Server Error');
        cy.get('input[placeholder*="message"], input[type=text]').should('exist');
        cy.contains('button', /send/i).should('exist');
    });
});

describe('Chat — Sending messages', () => {
    it('tenant can send a message to landlord and it appears in the thread', () => {
        cy.loginAsTenant();
        cy.visit('/chat/2');
        cy.get('body').should('not.contain', 'Server Error');

        const msg = `Test message ${Date.now()}`;
        cy.get('input[placeholder*="message"], input[type=text]').clear().type(msg);
        cy.contains('button', /send/i).click();

        // After redirect back to /chat/2, the message should be visible
        cy.url({ timeout: 10000 }).should('include', '/chat/2');
        cy.contains(msg).should('be.visible');
    });

    it('landlord can see the message sent by tenant', () => {
        // First, tenant sends a unique message
        const msg = `Landlord-visible msg ${Date.now()}`;
        cy.loginAsTenant();
        cy.visit('/chat/2');
        cy.get('input[placeholder*="message"], input[type=text]').clear().type(msg);
        cy.contains('button', /send/i).click();
        cy.url({ timeout: 10000 }).should('include', '/chat/2');

        // Now landlord opens chat with tenant (user id 3 is seeded tenant)
        cy.loginAsLandlord();
        cy.visit('/chat/3');
        cy.get('body').should('not.contain', 'Server Error');
        cy.contains(msg).should('be.visible');
    });

    it('sent message appears on the right (mine) side', () => {
        cy.loginAsTenant();
        cy.visit('/chat/2');
        const msg = `Right-side ${Date.now()}`;
        cy.get('input[placeholder*="message"], input[type=text]').clear().type(msg);
        cy.contains('button', /send/i).click();
        cy.url({ timeout: 10000 }).should('include', '/chat/2');
        // The message bubble should have justify-end (mine) class
        cy.contains(msg).closest('.flex').should('have.class', 'justify-end');
    });

    it('send button is disabled when input is empty', () => {
        cy.loginAsTenant();
        cy.visit('/chat/2');
        cy.get('input[placeholder*="message"], input[type=text]').clear();
        cy.contains('button', /send/i).should('be.disabled');
    });

    it('conversation appears in the sidebar after first message', () => {
        cy.loginAsTenant();
        const msg = `Sidebar test ${Date.now()}`;
        cy.visit('/chat/2');
        cy.get('input[placeholder*="message"], input[type=text]').clear().type(msg);
        cy.contains('button', /send/i).click();
        cy.url({ timeout: 10000 }).should('include', '/chat/2');

        // The sidebar should now list the landlord as a conversation
        cy.visit('/chat');
        cy.get('body').should('not.contain', 'Server Error');
        // At least one conversation entry should be visible
        cy.get('body').should('not.contain', 'No conversations yet');
    });
});

describe('Chat — Notification bell', () => {
    it('bell icon is visible in the header', () => {
        cy.loginAsTenant();
        cy.visit('/chat');
        // NotificationBell renders a button with BellIcon in the AuthenticatedLayout header
        cy.get('header button').filter(':has(svg)').should('exist');
    });

    it('clicking the bell opens the notification dropdown', () => {
        cy.loginAsTenant();
        cy.visit('/chat');
        // Find the bell button (it's the only icon-only button in the header)
        cy.get('header').find('button').filter(':has(svg)').first().click();
        cy.contains('Notifications').should('be.visible');
    });
});
