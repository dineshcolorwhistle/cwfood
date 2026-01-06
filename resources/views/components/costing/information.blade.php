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
            <p>The Batchbase app calculates product costs using a structured approach that factors in all production inputs. This ensures accurate cost analysis and helps you price products effectively. Below is a breakdown of how the costing system works:</p>
            <ol>
                <li><strong>Ingredients</strong><br>Costing starts with a bottom-up calculation based on the recipe. Each ingredient is costed per unit (e.g., per kilogram or litre) and then multiplied by the quantity used in the recipe. These individual ingredient costs are summed to determine the total ingredient cost for the product.</li>
                <li><strong>Labour</strong><br>Labour costs are calculated based on the time allocated to produce each product. The app considers the number of hours or minutes required, multiplied by the hourly labour rate. This accounts for direct labour efforts such as mixing, baking, or assembly.</li>
                <li><strong>Packaging</strong><br>The app includes the costs of all packaging materials required for the product, such as wrappers, trays, or containers. Additionally, it calculates the costs for cartons, pallets, or any bulk packaging used during shipping or storage. Each packaging component is itemised and summed for an accurate total.</li>
                <li><strong>Machinery</strong><br>Machinery costs are calculated based on hours of use during production. Each machine’s operating cost per hour is applied to the time it is used for the product. This captures expenses like maintenance, energy consumption, and wear and tear.</li>
                <li><strong>Contingency Factor</strong><br>A contingency factor is applied to cover unforeseen expenses, such as minor price fluctuations or waste. This ensures the costing is comprehensive and accounts for variability in production.</li>
                <li><strong>Cost Conversion to $/kg</strong><br>Once all inputs are totalled, the costs are converted to a dollar-per-kilogram ($/kg) value. This allows for consistent comparisons and scalability. The $/kg value is then multiplied by the weight of each selling unit, carton, or pallet to calculate the final cost for these selling configurations.</li>
            </ol>
            <p><strong>Summary</strong><br>By combining these factors, the Batchbase app provides a clear, accurate cost breakdown. The costs of ingredients, labour, packaging, machinery, and contingencies are aggregated, converted to $/kg, and scaled up for your chosen selling unit. This ensures your pricing reflects both production efficiency and market competitiveness.</p>
        </div>
    </div>
</div>