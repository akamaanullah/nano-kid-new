<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;

final class DealerController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminPage('admin/dealers/index', [
            'title' => 'Dealers — Admin',
            'page' => 'dealers',
            'dealers' => User::allDealers(),
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $dealer = User::find((int)$params['id']);
        if (!$dealer || $dealer['role'] !== 'dealer') {
            throw new HttpException(404, 'Dealer not found');
        }
        return $this->adminPage('admin/dealers/show', [
            'title' => $dealer['business_name'] ?: $dealer['name'],
            'page' => 'dealers',
            'dealer' => $dealer,
            'quotes' => Quote::forUser((int)$dealer['id']),
            'orders' => Order::forUser((int)$dealer['id']),
        ]);
    }

    public function toggle(Request $request, array $params): Response
    {
        $dealer = User::find((int)$params['id']);
        if (!$dealer || $dealer['role'] !== 'dealer') {
            throw new HttpException(404, 'Dealer not found');
        }
        $active = !$dealer['is_active'];
        User::setActive((int)$dealer['id'], (bool)$active);
        flash('success', $active ? 'Dealer reactivated.' : 'Dealer paused.');
        return $this->redirect('admin/dealers/' . $dealer['id']);
    }
}
