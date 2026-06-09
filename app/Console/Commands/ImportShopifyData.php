<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Food;
use App\Models\Blog;

class ImportShopifyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products as foods and articles as blogs from Shopify';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storeUrl = config('services.shopify.store_url');
        $accessToken = config('services.shopify.access_token');

        if (!$storeUrl || !$accessToken) {
            $this->error('Shopify credentials are missing in the services config.');
            return;
        }

        // Using latest stable API version
        $baseUrl = "https://{$storeUrl}/admin/api/2024-01";

        $this->info('Starting data import from Shopify...');

        DB::beginTransaction();

        try {
            $this->importFoods($baseUrl, $accessToken);
            $this->importBlogs($baseUrl, $accessToken);

            DB::commit();
            $this->info('All data successfully imported!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
        }
    }

    private function importFoods(string $baseUrl, string $accessToken): void
    {
        $this->info('Fetching products (foods)...');

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get("{$baseUrl}/products.json");

        if ($response->failed()) {
            throw new \Exception('Failed to fetch products from Shopify.');
        }

        $products = $response->json()['products'] ?? [];

        foreach ($products as $item) {
            // Determine category from product type, or use a default one
            $categoryName = !empty($item['product_type']) ? $item['product_type'] : 'General Food';

            // Fetch or create the category
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['status' => 'active']
            );

            // Determine status mapping
            $status = ($item['status'] === 'active') ? 'available' : 'unavailable';

            // Map and store product into foods table
            Food::updateOrCreate(
                ['name' => $item['title']], // Using name to prevent exact duplicates
                [
                    'category_id' => $category->id,
                    'description' => $item['body_html'] ?? null,
                    'price' => $item['variants'][0]['price'] ?? 0.00,
                    'stock' => $item['variants'][0]['inventory_quantity'] ?? 0,
                    'image' => $item['image']['src'] ?? null,
                    'status' => $status,
                ]
            );
        }

        $this->info(count($products) . ' foods processed.');
    }

    private function importBlogs(string $baseUrl, string $accessToken): void
    {
        $this->info('Fetching blogs and articles...');

        $blogResponse = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get("{$baseUrl}/blogs.json");

        if ($blogResponse->failed()) {
            throw new \Exception('Failed to fetch blogs from Shopify.');
        }

        $shopifyBlogs = $blogResponse->json()['blogs'] ?? [];
        $totalArticles = 0;

        foreach ($shopifyBlogs as $shopifyBlog) {
            // Shopify blog acts as a category for articles
            $category = Category::firstOrCreate(
                ['name' => $shopifyBlog['title']],
                ['status' => 'active']
            );

            // Fetch articles for this specific blog
            $articleResponse = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("{$baseUrl}/blogs/{$shopifyBlog['id']}/articles.json");

            $articles = $articleResponse->json()['articles'] ?? [];

            foreach ($articles as $article) {
                Blog::updateOrCreate(
                    ['title' => $article['title']], // Using title to prevent exact duplicates
                    [
                        'category_id' => $category->id,
                        'content' => $article['body_html'] ?? '',
                        'image' => $article['image']['src'] ?? null,
                        'status' => 'active',
                    ]
                );
                $totalArticles++;
            }
        }

        $this->info($totalArticles . ' blog articles processed.');
    }
}
