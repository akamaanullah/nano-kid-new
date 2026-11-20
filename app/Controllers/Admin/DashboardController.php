<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Application;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\PrivateLabel;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Karachi'));
        $d7 = $now->modify('-7 days')->format('Y-m-d H:i:s');
        $d14 = $now->modify('-14 days')->format('Y-m-d H:i:s');

        $quotesWeek = Quote::countSince($d7);
        $quotesPrev = Quote::countBetween($d14, $d7);
        $ordersWeek = Order::countSince($d7);
        $ordersPrev = Order::countBetween($d14, $d7);

        return $this->adminPage('admin/dashboard', [
            'title' => 'Dashboard — Admin',
            'page' => 'dashboard',
            'stats' => [
                'products' => Product::countActive(),
                'pending_apps' => Application::countPending(),
                'open_quotes' => Quote::countOpen(),
                'open_orders' => Order::countOpen(),
                'new_pl' => PrivateLabel::countNew(),
                'unread' => ContactMessage::countUnread(),
                'dealers' => count(User::allDealers()),
            ],
            'trends' => [
                'quotes' => self::trend($quotesWeek, $quotesPrev),
                'orders' => self::trend($ordersWeek, $ordersPrev),
                'quotes_week' => $quotesWeek,
                'orders_week' => $ordersWeek,
            ],
            'chart' => [
                'labels' => array_column(Quote::weeklyCounts(8), 'label'),
                'quotes' => array_column(Quote::weeklyCounts(8), 'count'),
                'orders' => array_column(Order::weeklyCounts(8), 'count'),
            ],
            'quotes' => array_slice(Quote::all(), 0, 6),
            'applications' => array_slice(Application::all('pending'), 0, 6),
            'messages' => array_slice(ContactMessage::all(), 0, 5),
        ]);
    }

    /** @return array{pct:int,dir:string,label:string} */
    private static function trend(int $current, int $previous): array
    {
        if ($previous === 0) {
            $pct = $current > 0 ? 100 : 0;
        } else {
            $pct = (int)round((($current - $previous) / $previous) * 100);
        }
        $dir = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat');
        $label = $pct === 0 ? 'Flat vs last week' : (abs($pct) . '% ' . ($pct > 0 ? 'up' : 'down') . ' vs last week');
        return ['pct' => $pct, 'dir' => $dir, 'label' => $label];
    }
}
