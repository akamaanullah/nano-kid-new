<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Models\Quote;

final class QuoteController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        $status = $request->string('status');
        return $this->adminPage('admin/quotes/index', [
            'title' => 'Quotes — Admin',
            'page' => 'quotes',
            'quotes' => Quote::all($status !== '' ? $status : null),
            'status' => $status,
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $quote = Quote::find((int)$params['id']);
        if (!$quote) {
            throw new HttpException(404, 'Quote not found');
        }
        return $this->adminPage('admin/quotes/show', [
            'title' => $quote['reference'] . ' — Admin',
            'page' => 'quotes',
            'quote' => $quote,
        ]);
    }

    public function status(Request $request, array $params): Response
    {
        $quote = Quote::find((int)$params['id']);
        if (!$quote) {
            throw new HttpException(404, 'Quote not found');
        }
        $status = $request->string('status');
        $allowed = ['submitted', 'reviewed', 'quoted', 'accepted', 'declined', 'converted'];
        if (!in_array($status, $allowed, true)) {
            flash('error', 'Invalid status.');
            return $this->redirect('admin/quotes/' . $quote['id']);
        }
        Quote::setStatus((int)$quote['id'], $status);
        flash('success', 'Quote status updated.');
        return $this->redirect('admin/quotes/' . $quote['id']);
    }

    public function convert(Request $request, array $params): Response
    {
        $quote = Quote::find((int)$params['id']);
        if (!$quote) {
            throw new HttpException(404, 'Quote not found');
        }
        if ($quote['status'] === 'converted') {
            flash('error', 'This quote is already an order.');
            return $this->redirect('admin/quotes/' . $quote['id']);
        }
        try {
            Database::begin();
            $orderId = Order::fromQuote($quote);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            app_log('quote.convert', ['error' => $e->getMessage()]);
            flash('error', 'Could not convert the quote.');
            return $this->redirect('admin/quotes/' . $quote['id']);
        }
        flash('success', 'Quote converted to an order.');
        return $this->redirect('admin/orders/' . $orderId);
    }
}
