<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Admin Variant Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style> [v-cloak] { display: none; } </style>
</head>
<body class="bg-gray-100 min-h-screen p-8 text-gray-800">

<div id="app" v-cloak class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Simple Variant Form (Manual Entry)</h1>
        
        <p class="text-gray-500 mb-8">
            This approach is much simpler. Instead of auto-generating combinations, the admin just manually adds rows for the variants they want to sell.
        </p>

        <!-- 1. Define Option Names -->
        <div class="mb-8 pb-8 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">What options does this product have?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Option 1 Name</label>
                    <input v-model="form.option1_name" type="text" placeholder="e.g. Protein" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:ring-2 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Option 2 Name (Optional)</label>
                    <input v-model="form.option2_name" type="text" placeholder="e.g. Sides" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:ring-2 outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Manual Variants Table -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Variants List</h2>
                <button @click="addVariantRow" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold">
                    + Add Variant Row
                </button>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                @{{ form.option1_name || 'Option 1' }} Value
                            </th>
                            <th v-if="form.option2_name" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                @{{ form.option2_name || 'Option 2' }} Value
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price ($)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(variant, idx) in form.variants" :key="idx">
                            <td class="px-4 py-3">
                                <input v-model="variant.option1" type="text" placeholder="e.g. Veggie" class="w-full px-3 py-1 border border-gray-300 rounded outline-none">
                            </td>
                            <td v-if="form.option2_name" class="px-4 py-3">
                                <input v-model="variant.option2" type="text" placeholder="e.g. Pancakes" class="w-full px-3 py-1 border border-gray-300 rounded outline-none">
                            </td>
                            <td class="px-4 py-3">
                                <input v-model="variant.price" type="number" step="0.01" class="w-24 px-3 py-1 border border-gray-300 rounded outline-none">
                            </td>
                            <td class="px-4 py-3">
                                <input v-model="variant.stock" type="number" class="w-20 px-3 py-1 border border-gray-300 rounded outline-none">
                            </td>
                            <td class="px-4 py-3">
                                <button @click="removeVariantRow(idx)" class="text-red-500 font-bold hover:text-red-700">✕</button>
                            </td>
                        </tr>
                        <tr v-if="form.variants.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
                                No variants added. Click "+ Add Variant Row" to start.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button @click="submitForm" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg">
                Save Product
            </button>
        </div>
    </div>

    <!-- JSON Preview -->
    <div v-if="showPayload" class="bg-gray-900 rounded-xl shadow-lg p-6 text-gray-300 overflow-hidden">
        <h2 class="text-green-400 font-bold mb-4">API JSON Payload</h2>
        <pre class="text-sm font-mono overflow-x-auto pb-4">@{{ JSON.stringify(finalPayload, null, 2) }}</pre>
    </div>
</div>

<script>
    const { createApp, ref, reactive } = Vue;

    createApp({
        setup() {
            const showPayload = ref(false);
            const finalPayload = ref({});

            const form = reactive({
                option1_name: 'Protein',
                option2_name: 'Sides',
                variants: [
                    { option1: 'Veggie', option2: 'Pancakes', price: 10, stock: 99 },
                    { option1: 'Bacon', option2: 'Pancakes', price: 11, stock: 50 }
                ]
            });

            const addVariantRow = () => {
                form.variants.push({ option1: '', option2: '', price: 0, stock: 0 });
            };

            const removeVariantRow = (index) => {
                form.variants.splice(index, 1);
            };

            const submitForm = () => {
                // 1. We manually build the options array for the backend based on what the admin typed
                let optionsPayload = [];
                
                if (form.option1_name) {
                    // Extract unique values the admin typed in the option1 inputs
                    let uniqueValues1 = [...new Set(form.variants.map(v => v.option1).filter(Boolean))];
                    optionsPayload.push({ name: form.option1_name, position: 1, values: uniqueValues1 });
                }

                if (form.option2_name) {
                    let uniqueValues2 = [...new Set(form.variants.map(v => v.option2).filter(Boolean))];
                    optionsPayload.push({ name: form.option2_name, position: 2, values: uniqueValues2 });
                }

                // 2. Final Payload Construction
                const payloadToSubmit = {
                    name: "Sample Product",
                    price: 10,
                    options: optionsPayload,
                    variants: form.variants.map(v => ({
                        price: parseFloat(v.price),
                        stock: parseInt(v.stock),
                        option1: v.option1,
                        option2: v.option2 || null
                    }))
                };

                finalPayload.value = payloadToSubmit;
                showPayload.value = true;
            };

            return {
                form,
                addVariantRow,
                removeVariantRow,
                submitForm,
                showPayload,
                finalPayload
            };
        }
    }).mount('#app');
</script>
</body>
</html>
