<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\AgeGroup;
use App\Models\Category;
use App\Models\Product;
use App\Services\Pricing;
use App\Services\Uploader;

final class ProductController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        $q = $request->string('q');
        $products = Product::allAdmin();
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $products = array_values(array_filter($products, static function (array $row) use ($needle): bool {
                $hay = mb_strtolower($row['name'] . ' ' . $row['sku'] . ' ' . ($row['category_label'] ?? ''));
                return str_contains($hay, $needle);
            }));
        }
        return $this->adminPage('admin/products/index', [
            'title' => 'Products — Admin',
            'page' => 'products',
            'products' => $products,
            'q' => $q,
        ]);
    }

    public function create(Request $request, array $params): Response
    {
        return $this->form(null);
    }

    public function store(Request $request, array $params): Response
    {
        return $this->save($request, null);
    }

    public function edit(Request $request, array $params): Response
    {
        $product = Product::find((int)$params['id']);
        if (!$product) {
            throw new HttpException(404, 'Product not found');
        }
        return $this->form($product);
    }

    public function update(Request $request, array $params): Response
    {
        $product = Product::find((int)$params['id']);
        if (!$product) {
            throw new HttpException(404, 'Product not found');
        }
        return $this->save($request, $product);
    }

    public function destroy(Request $request, array $params): Response
    {
        $id = (int)$params['id'];
        Product::deleteRow($id);
        flash('success', 'Product removed.');
        return $this->redirect('admin/products');
    }

    private function form(?array $product): Response
    {
        return $this->adminPage('admin/products/form', [
            'title' => ($product ? 'Edit ' . $product['name'] : 'New product') . ' — Admin',
            'page' => 'products',
            'product' => $product,
            'categories' => Category::all(false),
            'ages' => AgeGroup::all(false),
            'colors' => setting_json('colors', []),
            'seasons' => setting_json('seasons', []),
        ]);
    }

    private function save(Request $request, ?array $existing): Response
    {
        $id = $existing['id'] ?? null;
        $name = $request->string('name');
        $sku = strtoupper($request->string('sku'));
        $slug = slugify($request->string('slug') ?: $name);
        $moq = $request->int('moq', 0);
        $price = (float)str_replace(',', '', $request->string('base_price', '0'));
        $tiers = $this->parseTiers($request);

        $errors = [];
        if ($name === '' || $sku === '') {
            $errors[] = 'Name and SKU are required.';
        }
        if ($moq < 1) {
            $errors[] = 'MOQ must be at least 1.';
        }
        if ($price <= 0) {
            $errors[] = 'Base price must be greater than zero.';
        }
        if (!Product::uniqueSku($sku, $id ? (int)$id : null)) {
            $errors[] = 'That SKU is already in use.';
        }
        $errors = array_merge($errors, Pricing::validateTiers($moq, $tiers));

        $images = [];
        foreach ((array)$request->input('image_urls', []) as $url) {
            $url = trim((string)$url);
            if ($url !== '') {
                $images[] = $url;
            }
        }
        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file((string)$_FILES['image']['tmp_name'])) {
            try {
                $stored = Uploader::image($_FILES['image'], 'products');
                if ($stored) {
                    array_unshift($images, upload_url($stored));
                }
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }
        // keep existing images if editing and none provided
        if ($existing && !$images && !empty($existing['images'])) {
            $images = $existing['images'];
        }

        $sizes = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $request->string('sizes')) ?: [])));
        $colors = (array)$request->input('colors', []);
        $colors = array_values(array_filter(array_map('strval', $colors)));

        if ($errors) {
            flash('error', implode(' ', $errors));
            flash('_old', $request->all());
            return $this->redirect($id ? 'admin/products/' . $id . '/edit' : 'admin/products/create');
        }

        $data = [
            'sku' => $sku,
            'name' => $name,
            'slug' => Product::uniqueSlug($slug, $id ? (int)$id : null),
            'category_id' => $request->int('category_id'),
            'age_group_id' => $request->int('age_group_id'),
            'season' => $request->string('season', 'core'),
            'fabric' => $request->string('fabric'),
            'gsm' => $request->string('gsm'),
            'moq' => $moq,
            'base_price' => $price,
            'lead_time_days' => $request->int('lead_time_days', 7),
            'stock_status' => $request->string('stock_status', 'in_stock'),
            'description' => $request->string('description'),
            'tag' => $request->string('tag') ?: null,
            'pack_units' => $request->string('pack_units'),
            'pack_ratio' => $request->string('pack_ratio') ?: null,
            'is_active' => $request->input('is_active') ? 1 : 0,
        ];

        try {
            Database::begin();
            if ($id) {
                Product::updateRow((int)$id, $data);
                $productId = (int)$id;
            } else {
                $productId = Product::create($data);
            }
            Product::syncRelations($productId, $images, $sizes, $colors, $tiers);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            app_log('admin.product.save', ['error' => $e->getMessage()]);
            flash('error', 'Could not save the product.');
            return $this->redirect($id ? 'admin/products/' . $id . '/edit' : 'admin/products/create');
        }

        flash('success', 'Product saved.');
        return $this->redirect('admin/products/' . $productId . '/edit');
    }

    private function parseTiers(Request $request): array
    {
        $mins = (array)$request->input('tier_min', []);
        $maxs = (array)$request->input('tier_max', []);
        $offs = (array)$request->input('tier_discount', []);
        $tiers = [];
        foreach ($mins as $i => $min) {
            if ($min === '' || $min === null) {
                continue;
            }
            $max = $maxs[$i] ?? '';
            $tiers[] = [
                'min' => (int)$min,
                'max' => $max === '' ? null : (int)$max,
                'discount' => (float)($offs[$i] ?? 0),
            ];
        }
        usort($tiers, static fn($a, $b) => $a['min'] <=> $b['min']);
        return $tiers;
    }
}
