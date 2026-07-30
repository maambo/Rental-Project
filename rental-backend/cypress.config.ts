import { defineConfig } from 'cypress';
import { exec } from 'child_process';
import { fileURLToPath } from 'url';
import * as path from 'path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    e2e: {
        // Herd (APP_URL) rather than `php artisan serve`. PHP's built-in server
        // cannot create upload temp files on Windows, which fails every test that
        // uploads a file (registration NRC/selfie, landlord docs, property images).
        // Override with CYPRESS_BASE_URL if you serve the app another way.
        baseUrl: 'http://rental-backend.test',
        viewportWidth: 1280,
        viewportHeight: 800,
        specPattern: 'cypress/e2e/**/*.cy.ts',
        supportFile: 'cypress/support/e2e.ts',
        video: false,
        screenshotOnRunFailure: true,
        setupNodeEvents(on) {
            on('task', {
                cleanFlowTestData() {
                    return new Promise((resolve, reject) => {
                        // Laravel Herd PHP — `php` may not be on PATH in the Cypress Node process
                        const phpBin = process.env.PHP_BIN ?? 'php';
                        exec(
                            `"${phpBin}" artisan test:cleanup-flow`,
                            { cwd: path.resolve(__dirname) },
                            (err, stdout, stderr) => {
                                if (err) reject(stderr || err.message);
                                else resolve(stdout.trim());
                            },
                        );
                    });
                },
            });
        },
    },
});
