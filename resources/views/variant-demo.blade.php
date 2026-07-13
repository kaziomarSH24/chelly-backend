<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fully Dynamic Variant Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .option-btn { transition: all 0.2s ease-in-out; }
        .option-btn.selected {
            background-color: #111827; color: white; border-color: #111827;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <!-- Product Card Container -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-4xl w-full flex flex-col md:flex-row">
        
        <!-- Left Side: Product Image -->
        <div class="md:w-1/2 bg-gray-100 flex items-center justify-center p-8">
            <img id="product-image" src="" alt="Product Image" class="max-w-full h-auto drop-shadow-2xl rounded-lg transform hover:scale-105 transition duration-500">
        </div>

        <!-- Right Side: Product Details & Variant Selection -->
        <div class="md:w-1/2 p-8 lg:p-12 flex flex-col justify-center">
            
            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold mb-1" id="category-name">Category</div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2" id="product-name">Product Name</h1>
            
            <!-- Dynamic Price Display -->
            <div class="text-4xl font-black text-gray-900 mb-6 flex items-center gap-2">
                $<span id="price-display">0.00</span>
            </div>

            <div class="text-gray-500 mb-8 leading-relaxed line-clamp-3 hover:line-clamp-none transition-all text-sm" id="product-description">
            </div>

            <!-- DYNAMIC OPTIONS CONTAINER: Everything injected via JS -->
            <div id="dynamic-options-container" class="space-y-6">
            </div>

            <button class="mt-10 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-indigo-300">
                Add to Cart
            </button>
        </div>
    </div>

    <script>
        // 1. Exact API Payload
        const mockApiData = {
            "id": 72,
            "category_id": 20,
            "name": "3 Egg Omlette",
            "description": "<p>Indulge in our expertly crafted 3 Egg Omelette, a protein-rich breakfast option prepared with three farm-fresh eggs for optimal fluffiness and texture. Customize your culinary experience by selecting your preferred protein—whether it's savory bacon, ham, sausage, or plant-based alternatives. Further enhance your omelette with an array of fresh toppings including bell peppers, onions, mushrooms, tomatoes, spinach, and premium cheeses. Each omelette is cooked to perfection, ensuring a golden exterior while maintaining a tender interior. An ideal choice for those seeking a nutritious, satisfying meal to fuel your day.</p>",
            "price": "10.00",
            "stock": 99,
            "options": [
                {
                    "name": "Protein",
                    "values": ["Ham", "Cheese", "Veggie", "Bacon", "Steak (+$3.00)"],
                    "position": 1
                },
                {
                    "name": "Sides",
                    "values": ["Pancakes", "French Toast", "Waffles", "Parfait", "Potato Wedges", "Oats", "Fruit Balls"],
                    "position": 2
                }
            ],
            "image": "https://cdn.shopify.com/s/files/1/0684/1060/5744/files/3_Egg_Omlette.png?v=1748119540",
            "category": { "name": "Prepared Meals & Entrées" },
            "variants": [
                {"shopify_variant_id": "44379262550192", "title": "Ham / Pancakes", "price": "10.00", "option1": "Ham", "option2": "Pancakes"},
                {"shopify_variant_id": "44379262976176", "title": "Ham / French Toast", "price": "10.00", "option1": "Ham", "option2": "French Toast"},
                {"shopify_variant_id": "44379262648496", "title": "Bacon / Pancakes", "price": "11.00", "option1": "Bacon", "option2": "Pancakes"},
                {"shopify_variant_id": "44379263566000", "title": "Bacon / French Toast", "price": "11.00", "option1": "Bacon", "option2": "French Toast"},
                {"shopify_variant_id": "44379262681264", "title": "Steak (+$3.00) / Pancakes", "price": "13.00", "option1": "Steak (+$3.00)", "option2": "Pancakes"},
                {"shopify_variant_id": "44379263762608", "title": "Steak (+$3.00) / French Toast", "price": "13.00", "option1": "Steak (+$3.00)", "option2": "French Toast"}
                // Note: Truncated some variants to keep demo file clean, but logic works for all.
            ]
        };

        // 2. State object to keep track of user's selections
        // It will look like: { option1: "Ham", option2: "Pancakes" }
        const selectedState = {};

        // 3. Initialize the UI
        function initApp() {
            document.getElementById('product-name').innerText = mockApiData.name;
            document.getElementById('category-name').innerText = mockApiData.category.name;
            document.getElementById('product-description').innerHTML = mockApiData.description; // Using innerHTML to render <p> tags
            document.getElementById('product-image').src = mockApiData.image;

            const container = document.getElementById('dynamic-options-container');
            
            // Generate UI dynamically from the `options` array
            if (mockApiData.options && mockApiData.options.length > 0) {
                mockApiData.options.forEach((optionGroup, index) => {
                    const optionKey = `option${index + 1}`; // Creates 'option1', 'option2' etc.
                    
                    // Set default selection to the first value
                    if (optionGroup.values && optionGroup.values.length > 0) {
                        selectedState[optionKey] = optionGroup.values[0];
                    }

                    // Create the wrapper for this option group
                    const groupDiv = document.createElement('div');
                    
                    // Add the title (e.g. "Protein")
                    const label = document.createElement('h3');
                    label.className = "text-sm font-bold text-gray-900 uppercase tracking-wider mb-3";
                    label.innerText = optionGroup.name; 
                    groupDiv.appendChild(label);

                    // Add the buttons
                    const buttonsDiv = document.createElement('div');
                    buttonsDiv.className = "flex flex-wrap gap-3";
                    
                    optionGroup.values.forEach(val => {
                        const btn = document.createElement('button');
                        btn.innerText = val;
                        btn.className = `option-btn px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium bg-white hover:bg-gray-50 focus:outline-none ${selectedState[optionKey] === val ? 'selected' : ''}`;
                        
                        btn.onclick = () => {
                            // Visual update
                            Array.from(buttonsDiv.children).forEach(child => child.classList.remove('selected'));
                            btn.classList.add('selected');
                            
                            // State update & price recalculation
                            selectedState[optionKey] = val;
                            updatePrice();
                        };
                        buttonsDiv.appendChild(btn);
                    });
                    
                    groupDiv.appendChild(buttonsDiv);
                    container.appendChild(groupDiv);
                });
            }

            // Set initial price
            updatePrice();
        }

        // 4. Update Price dynamically by finding the matching variant
        function updatePrice() {
            const matchedVariant = mockApiData.variants.find(v => {
                let isMatch = true;
                // Check all available option keys against our selected state
                if (mockApiData.options[0] && v.option1 !== selectedState.option1) isMatch = false;
                if (mockApiData.options[1] && v.option2 !== selectedState.option2) isMatch = false;
                if (mockApiData.options[2] && v.option3 !== selectedState.option3) isMatch = false;
                return isMatch;
            });

            const priceEl = document.getElementById('price-display');
            if (matchedVariant) {
                priceEl.innerText = parseFloat(matchedVariant.price).toFixed(2);
                // Fun micro-animation
                priceEl.classList.add('scale-110', 'text-indigo-600');
                setTimeout(() => priceEl.classList.remove('scale-110', 'text-indigo-600'), 200);
            } else {
                priceEl.innerText = parseFloat(mockApiData.price).toFixed(2);
            }
        }

        // Run the app!
        initApp();
    </script>
</body>
</html>
