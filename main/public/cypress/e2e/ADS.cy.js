describe('Search Functionality', () => {
    it('Simple sanity check', () => {
        // Visit the application's URL
        cy.visit('https://ads.legion.si/'); // Replace with your application's URL
    });
    it('Should search for a location and display agent information', () => {
        // Visit the application's URL
        cy.visit('https://ads.legion.si/'); // Replace with your application's URL

        // Type a location in the search input
        cy.get('#address').type('Leskovškova 2, 1000 Ljubljana ');

        // Click the "Search" button
        cy.get('.go').click();

        // Check that the agent information is displayed
        cy.get('.agent').should('have.length.greaterThan', 0);
    });
    it('Should rebuild our database and it should still work', () => {
        // Rebuild database
        cy.request('https://ads.legion.si/api.php?action=build', {timeout: 30000}); // Replace with your application's URL

        // Visit the application's URL
        cy.visit('https://ads.legion.si/'); // Replace with your application's URL

        // Type a location in the search input
        cy.get('#address').type('Leskovškova 2, 1000 Ljubljana ');

        // Click the "Search" button
        cy.get('.go').click();

        // Check that the agent information is displayed
        cy.get('.agent').should('have.length.greaterThan', 0);
    });
});