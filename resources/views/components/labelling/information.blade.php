<style>
/* Remove Bootstrap default arrow */
.accordion-button::after {display: none;}
/* Add our own + / - icon */
.accordion-button::before { content: '+'; font-size: 1.25rem; font-weight: bold; margin-right: 10px; }
/* Change to - when open */
.accordion-button:not(.collapsed)::before {content: '–';}
.accordion-body li{color: var(--bs-dark-mud) !important;opacity: 1 !important;font-size: 14px !important;font-weight: 400 !important;}
.accordion-button{color: var(--dark-gray-font);font-size: 14px; font-weight: 700;}
.accordion-button:not(.collapsed){color: var(--secondary-color); background-color: var(--secondary-light-primary-color-10);font-size: 14px; font-weight: 700;}
.accordion-button:focus{box-shadow: none;}
</style>
<div class="card price_card mb-3 p-3 rounded-2">
    <div class="card-body px-0 py-2">
        <div class="accordion-body">
            <p>Creating Labels in the Batchbase App: Best Practices<br>
                Follow these guidelines to create compliant and professional food labels using the Batchbase app:</p>
            <ol>
                <li><strong>Identify the Product Clearly </strong><br>Ensure the product name accurately reflects its nature. For example, “Strawberry Yoghurt” must contain real strawberries, while “Strawberry-Flavoured Yoghurt” indicates artificial flavouring. Include supplier details (business name and address) and a lot or batch number for traceability.</li>
                <li><strong>List Ingredients Properly</strong><br>Enter all ingredients in descending order by weight as added during production. For compound ingredients (e.g., chocolate with cocoa, sugar, and milk), specify the components unless the compound makes up less than 5% of the product and does not contain allergens.</li>
                <li><strong>Include Percentage Labelling</strong><br>Highlight the percentage of key or characterising ingredients. For instance, in “20% Strawberry Yoghurt,” the percentage of strawberries must be specified.</li>
                <li><strong>Declare Allergens Clearly</strong><br>Use bold and plain English to identify mandatory allergens such as peanuts, milk, eggs, and soybeans. Place an allergen summary statement (e.g., “Contains milk, eggs”) near the ingredient list for quick reference.</li>
                <li><strong>Add Date Marking</strong><br>For perishable foods, include a use-by date to indicate safety. For other items, use a best-before date to show optimal quality.</li>
                <li><strong>Create a Nutrition Information Panel (NIP)</strong><br>Include the average quantities per serving and per 100 g/mL for energy, protein, total fat, saturated fat, carbohydrate, sugars, and sodium. If a nutrient claim is made (e.g., “high in fibre”), include the relevant nutrient in the panel.</li>
                <li><strong>Provide Usage and Storage Instructions</strong><br>Specify storage conditions (e.g., “Keep refrigerated below 5°C”) and usage instructions if necessary for safety or quality maintenance.</li>
                <li><strong>Indicate the Country of Origin</strong><br>Include the country of origin on all packaged foods. In New Zealand, this requirement applies to wine only.</li>
                <li><strong>Ensure Legibility and Accuracy</strong><br>Labels must be in English, easy to read, and prominently displayed. Avoid misleading claims and use accurate measures. Food additives should be identified by their class name (e.g., “Thickener”) followed by the additive’s name or number.</li>
            </ol>
            <p>By following these steps in the Batchbase app, you can ensure your labels meet industry standards and provide clear, accurate information to consumers. For detailed requirements, refer to the FSANZ labelling guide.</p>
        </div>
    </div>
</div>