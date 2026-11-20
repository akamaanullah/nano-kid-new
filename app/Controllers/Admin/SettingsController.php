<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Setting;

final class SettingsController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminPage('admin/settings/index', [
            'title' => 'Settings — Admin',
            'page' => 'settings',
            'site_name' => setting('site_name', 'Nano Kids Wholesale'),
            'contact' => setting_json('trade_contact', []),
            'manager' => setting_json('account_manager', []),
        ]);
    }

    public function save(Request $request, array $params): Response
    {
        Setting::set('site_name', $request->string('site_name') ?: 'Nano Kids Wholesale');
        $contact = [
            'email' => $request->string('contact_email'),
            'phone' => $request->string('contact_phone'),
            'whatsapp' => $request->string('contact_whatsapp'),
            'warehouse' => $request->string('contact_warehouse'),
            'showroom' => $request->string('contact_showroom'),
        ];
        $manager = [
            'name' => $request->string('manager_name'),
            'role' => $request->string('manager_role'),
            'phone' => $request->string('manager_phone'),
            'email' => $request->string('manager_email'),
            'hours' => $request->string('manager_hours'),
        ];
        Setting::set('trade_contact', json_encode($contact, JSON_UNESCAPED_UNICODE));
        Setting::set('account_manager', json_encode($manager, JSON_UNESCAPED_UNICODE));
        flash('success', 'Settings saved.');
        return $this->redirect('admin/settings');
    }
}
