<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;

final class OrderController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminPage('admin/orders/index', [
            'title' => 'Orders — Admin',
            'page' => 'orders',
            'orders' => Order::all(),
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $order = Order::find((int)$params['id']);
        if (!$order) {
            throw new HttpException(404, 'Order not found');
        }
        return $this->adminPage('admin/orders/show', [
            'title' => $order['reference'] . ' — Admin',
            'page' => 'orders',
            'order' => $order,
        ]);
    }

    public function status(Request $request, array $params): Response
    {
        $order = Order::find((int)$params['id']);
        if (!$order) {
            throw new HttpException(404, 'Order not found');
        }
        $status = $request->string('status');
        $allowed = ['pending', 'confirmed', 'in_packing', 'dispatched', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            flash('error', 'Invalid status.');
            return $this->redirect('admin/orders/' . $order['id']);
        }
        Order::setStatus((int)$order['id'], $status);
        flash('success', 'Order status updated.');
        return $this->redirect('admin/orders/' . $order['id']);
    }
}
