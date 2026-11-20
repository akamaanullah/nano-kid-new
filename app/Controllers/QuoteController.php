<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Product;
use App\Models\Quote;
use App\Services\Pricing;

final class QuoteController extends Controller
{
    public function show(Request $request, array $params): Response
    {
        $user = current_user();
        return $this->page('store/quote', [
            'title' => 'Your quote list — Nano Kids Wholesale',
            'description' => 'Review your wholesale quote, confirm MOQs and send an RFQ.',
            'page' => 'quote',
            'boot' => store_boot(),
            'user' => $user,
        ]);
    }

    public function submit(Request $request, array $params): Response
    {
        $payload = $request->json();
        if (!$payload) {
            $payload = $request->all();
            if (isset($payload['items']) && is_string($payload['items'])) {
                $decoded = json_decode($payload['items'], true);
                $payload['items'] = is_array($decoded) ? $decoded : [];
            }
        }

        $itemsIn = $payload['items'] ?? [];
        if (!is_array($itemsIn) || !$itemsIn) {
            return $this->fail($request, ['Your quote list is empty.'], 422);
        }

        $user = current_user();
        $business = trim((string)($payload['business'] ?? $payload['business_name'] ?? ($user['business_name'] ?? '')));
        $contact = trim((string)($payload['contact'] ?? $payload['contact_name'] ?? ($user['name'] ?? '')));
        $email = trim((string)($payload['email'] ?? ($user['email'] ?? '')));
        $phone = trim((string)($payload['phone'] ?? ($user['phone'] ?? '')));
        $city = trim((string)($payload['city'] ?? ($user['city'] ?? '')));
        $ntn = trim((string)($payload['ntn'] ?? ($user['ntn'] ?? '')));
        $notes = trim((string)($payload['notes'] ?? ''));

        $errors = [];
        if ($business === '') {
            $errors[] = 'Business name is required.';
        }
        if ($contact === '') {
            $errors[] = 'Contact person is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid work email is required.';
        }
        if ($phone === '') {
            $errors[] = 'Phone is required.';
        }
        if ($city === '') {
            $errors[] = 'City is required.';
        }

        $priced = [];
        $pieces = 0;
        $subtotal = 0.0;

        foreach ($itemsIn as $i => $line) {
            if (!is_array($line)) {
                continue;
            }
            $isCustom = !empty($line['custom']) || !empty($line['is_custom']) || (($line['type'] ?? '') === 'private-label');
            $qty = $this->lineQty($line);
            if ($qty <= 0) {
                $errors[] = 'Line ' . ($i + 1) . ' has no quantity.';
                continue;
            }

            if ($isCustom) {
                $moq = 50;
                if ($qty < $moq) {
                    $errors[] = 'Private-label programmes require at least ' . $moq . ' pieces.';
                    continue;
                }
                $name = (string)($line['name'] ?? $line['shop'] ?? 'Private label programme');
                $unit = isset($line['unit_price']) ? (float)$line['unit_price'] : 0.0;
                $priced[] = [
                    'product_id' => null,
                    'sku' => null,
                    'name' => $name,
                    'color' => $line['color'] ?? null,
                    'sizes' => $line['sizes'] ?? null,
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'discount_pct' => 0,
                    'line_total' => round($unit * $qty, 2),
                    'moq' => $moq,
                    'is_custom' => 1,
                    'custom_meta' => $line['meta'] ?? $line,
                ];
                $pieces += $qty;
                $subtotal += $unit * $qty;
                continue;
            }

            $product = $this->resolveProduct($line);
            if (!$product) {
                $errors[] = 'Line ' . ($i + 1) . ' refers to an unknown style.';
                continue;
            }
            $moq = (int)$product['moq'];
            if ($qty < $moq) {
                $errors[] = $product['name'] . ' requires a minimum of ' . $moq . ' pieces (you entered ' . $qty . ').';
                continue;
            }
            $applied = Pricing::priceFor((float)$product['base_price'], $product['tiers'], $qty);
            $lineTotal = round($applied['unit'] * $qty, 2);
            $priced[] = [
                'product_id' => $product['id'],
                'sku' => $product['sku'],
                'name' => $product['name'],
                'color' => $line['color'] ?? null,
                'sizes' => $line['sizes'] ?? null,
                'qty' => $qty,
                'unit_price' => $applied['unit'],
                'discount_pct' => $applied['discount'],
                'line_total' => $lineTotal,
                'moq' => $moq,
                'is_custom' => 0,
                'custom_meta' => null,
            ];
            $pieces += $qty;
            $subtotal += $lineTotal;
        }

        if ($errors) {
            return $this->fail($request, $errors, 422);
        }
        if (!$priced) {
            return $this->fail($request, ['Your quote list is empty.'], 422);
        }

        $userId = null;
        if ($user && ($user['role'] ?? '') === 'dealer') {
            $userId = (int)$user['id'];
        }

        try {
            Database::begin();
            $ref = Quote::nextReference();
            $id = Quote::create([
                'reference' => $ref,
                'user_id' => $userId,
                'business_name' => $business,
                'contact_name' => $contact,
                'email' => $email,
                'phone' => $phone,
                'city' => $city,
                'ntn' => $ntn,
                'notes' => $notes,
                'pieces' => $pieces,
                'subtotal' => round($subtotal, 2),
            ], $priced);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            app_log('quote.submit', ['error' => $e->getMessage()]);
            return $this->fail($request, ['The quote could not be saved. Please try again.'], 500);
        }

        $quote = Quote::find($id);
        if ($request->wantsJson() || $request->isPost() && ($payload['items'] ?? null)) {
            return $this->json([
                'ok' => true,
                'reference' => $ref,
                'id' => $id,
                'pieces' => $pieces,
                'subtotal' => round($subtotal, 2),
                'quote' => $quote,
            ]);
        }
        flash('success', 'Quote ' . $ref . ' is with the desk.');
        return $this->redirect('quote?submitted=' . urlencode($ref));
    }

    private function fail(Request $request, array $errors, int $status): Response
    {
        if ($request->wantsJson() || $request->method() === 'POST') {
            // quote form posts JSON from JS; always return JSON when Accept/ajax or typical fetch
            $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
            $isFetch = str_contains($accept, 'application/json') || isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $request->json();
            if ($isFetch || $request->wantsJson()) {
                return $this->json(['ok' => false, 'errors' => $errors], $status);
            }
        }
        flash('error', implode(' ', $errors));
        return $this->redirect('quote');
    }

    private function lineQty(array $line): int
    {
        if (isset($line['qty']) && is_numeric($line['qty'])) {
            return (int)$line['qty'];
        }
        if (isset($line['sizes']) && is_array($line['sizes'])) {
            $n = 0;
            foreach ($line['sizes'] as $q) {
                $n += (int)$q;
            }
            return $n;
        }
        return 0;
    }

    private function resolveProduct(array $line): ?array
    {
        if (!empty($line['product_id']) && is_numeric($line['product_id'])) {
            return Product::find((int)$line['product_id']);
        }
        if (!empty($line['id']) && is_numeric($line['id'])) {
            return Product::find((int)$line['id']);
        }
        if (!empty($line['slug'])) {
            return Product::findBySlug((string)$line['slug']);
        }
        if (!empty($line['sku'])) {
            // slim fallback
            $all = Product::search(['q' => (string)$line['sku']], 1, 5, 'featured');
            foreach ($all['items'] as $p) {
                if (strcasecmp($p['sku'], (string)$line['sku']) === 0) {
                    return $p;
                }
            }
        }
        return null;
    }
}
