<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AgeGroup;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\PrivateLabel;
use App\Models\Product;

final class StoreController extends Controller
{
    public function home(Request $request, array $params): Response
    {
        $campaign = setting_json('campaign', []);
        return $this->page('store/home', [
            'title' => 'Nano Kids Wholesale — Trade catalogue',
            'description' => 'Kids and baby garments in wholesale packs. Per-style MOQs, tier pricing and private label from our Lahore desk.',
            'page' => 'home',
            'featured' => Product::featured(8),
            'ages' => AgeGroup::all(),
            'categories' => Category::all(),
            'campaign' => $campaign,
            'boot' => store_boot(),
        ]);
    }

    public function catalog(Request $request, array $params): Response
    {
        $filters = [
            'q' => $request->string('q'),
            'category' => $request->string('category') ?: $request->string('cat'),
            'age' => $request->string('age'),
            'season' => $request->string('season'),
            'status' => $request->string('status') ?: $request->string('stock'),
            'fabric' => $request->string('fabric'),
            'moq' => $request->string('moq'),
            'min' => $request->string('min'),
            'max' => $request->string('max'),
        ];
        $sort = $request->string('sort', 'featured');
        $page = max(1, $request->int('page', 1));
        $result = Product::search($filters, $page, 12, $sort);

        if ($request->wantsJson() || $request->query('ajax')) {
            return $this->json([
                'ok' => true,
                'items' => $result['items'],
                'total' => $result['total'],
                'page' => $result['page'],
                'pages' => $result['pages'],
                'per' => $result['per'],
            ]);
        }

        return $this->page('store/catalog', [
            'title' => 'Catalogue — Nano Kids Wholesale',
            'description' => 'Browse the Nano Kids wholesale catalogue. Filter by category, age and season. Each style has its own MOQ and tier pricing.',
            'page' => 'catalog',
            'filters' => $filters,
            'sort' => $sort,
            'result' => $result,
            'ages' => AgeGroup::all(),
            'categories' => Category::all(),
            'seasons' => setting_json('seasons', []),
            'boot' => store_boot(),
        ]);
    }

    public function product(Request $request, array $params): Response
    {
        $slug = (string)($params['slug'] ?? '');
        $product = Product::findBySlug($slug);
        if (!$product) {
            throw new \App\Core\HttpException(404, 'That style is not in the trade book.');
        }
        $related = Product::related($product, 4);
        return $this->page('store/product', [
            'title' => $product['name'] . ' — Nano Kids Wholesale',
            'description' => $product['description'] ?: ($product['name'] . ' wholesale. MOQ ' . $product['moq'] . ' pieces.'),
            'page' => 'product',
            'product' => $product,
            'related' => $related,
            'boot' => store_boot(['product' => $product]),
        ]);
    }

    public function about(Request $request, array $params): Response
    {
        return $this->page('store/about', [
            'title' => 'About the trade desk — Nano Kids Wholesale',
            'description' => 'How Nano Kids works with approved dealers from our Lahore warehouse and showroom.',
            'page' => 'about',
            'boot' => store_boot(),
        ]);
    }

    public function faq(Request $request, array $params): Response
    {
        return $this->page('store/faq', [
            'title' => 'Trade FAQ — Nano Kids Wholesale',
            'description' => 'MOQs, lead times, private label and dealership questions.',
            'page' => 'faq',
            'boot' => store_boot(),
        ]);
    }

    public function contact(Request $request, array $params): Response
    {
        return $this->page('store/contact', [
            'title' => 'Contact the trade desk — Nano Kids Wholesale',
            'description' => 'Reach the Nano Kids trade desk in Lahore.',
            'page' => 'contact',
            'boot' => store_boot(),
        ]);
    }

    public function contactSubmit(Request $request, array $params): Response
    {
        $business = $request->string('business');
        $email = $request->string('email');
        $phone = $request->string('phone');
        $message = $request->string('message');
        $errors = [];
        if ($business === '') {
            $errors[] = 'Please enter your business name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid work email.';
        }
        if ($phone === '') {
            $errors[] = 'Please enter a phone number.';
        }
        if (strlen($message) < 8) {
            $errors[] = 'Please include a short message.';
        }
        if ($errors) {
            if ($request->wantsJson()) {
                return $this->json(['ok' => false, 'errors' => $errors], 422);
            }
            flash('error', implode(' ', $errors));
            flash('_old', $request->all());
            return $this->redirect('contact');
        }
        ContactMessage::create([
            'business_name' => $business,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
        ]);
        if ($request->wantsJson()) {
            return $this->json(['ok' => true, 'message' => 'Message received. The desk will reply shortly.']);
        }
        flash('success', 'Message received. The desk will reply shortly.');
        return $this->redirect('contact');
    }

    public function privateLabel(Request $request, array $params): Response
    {
        return $this->page('store/private-label', [
            'title' => 'Private label programmes — Nano Kids Wholesale',
            'description' => 'Bulk custom design: logo, labels and packaging on a 50-piece minimum.',
            'page' => 'private-label',
            'boot' => store_boot(),
        ]);
    }

    public function privateLabelSubmit(Request $request, array $params): Response
    {
        $qty = $request->int('qty', 0);
        $shop = $request->string('shop_name') ?: $request->string('name');
        $errors = [];
        if ($shop === '') {
            $errors[] = 'Please enter a shop or brand name.';
        }
        if ($qty < 50) {
            $errors[] = 'Private-label programmes start at 50 pieces.';
        }
        if ($errors) {
            if ($request->wantsJson()) {
                return $this->json(['ok' => false, 'errors' => $errors], 422);
            }
            flash('error', implode(' ', $errors));
            return $this->redirect('private-label');
        }
        $user = current_user();
        $id = PrivateLabel::create([
            'user_id' => $user['id'] ?? null,
            'shop_name' => $shop,
            'email' => $request->string('email') ?: ($user['email'] ?? null),
            'phone' => $request->string('phone') ?: ($user['phone'] ?? null),
            'garment' => $request->string('garment', 'tee'),
            'mark' => $request->string('mark', 'print'),
            'label_type' => $request->string('label', 'woven'),
            'pack' => $request->string('pack', 'mailer'),
            'color' => $request->string('color'),
            'qty' => $qty,
            'notes' => $request->string('notes'),
        ]);
        if ($request->wantsJson()) {
            return $this->json(['ok' => true, 'id' => $id, 'message' => 'Programme request received.']);
        }
        flash('success', 'Programme request received. The desk will follow up.');
        return $this->redirect('private-label');
    }
}
