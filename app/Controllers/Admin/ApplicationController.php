<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Application;
use App\Models\User;

final class ApplicationController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        $status = $request->string('status');
        return $this->adminPage('admin/applications/index', [
            'title' => 'Dealer applications — Admin',
            'page' => 'applications',
            'applications' => Application::all($status !== '' ? $status : null),
            'status' => $status,
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $app = Application::find((int)$params['id']);
        if (!$app) {
            throw new HttpException(404, 'Application not found');
        }
        return $this->adminPage('admin/applications/show', [
            'title' => 'Application — ' . $app['business_name'],
            'page' => 'applications',
            'application' => $app,
        ]);
    }

    public function approve(Request $request, array $params): Response
    {
        $app = Application::find((int)$params['id']);
        if (!$app) {
            throw new HttpException(404, 'Application not found');
        }
        if ($app['status'] === 'approved') {
            flash('error', 'This application is already approved.');
            return $this->redirect('admin/applications/' . $app['id']);
        }
        $password = (string)$request->input('password', '');
        if (strlen($password) < 8) {
            flash('error', 'Set a password of at least 8 characters for the new dealer login.');
            return $this->redirect('admin/applications/' . $app['id']);
        }
        if (User::findByEmail($app['email'])) {
            flash('error', 'A user already exists with this email.');
            return $this->redirect('admin/applications/' . $app['id']);
        }
        $admin = current_user();
        try {
            Database::begin();
            $userId = User::create([
                'role' => 'dealer',
                'email' => $app['email'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $app['contact_name'],
                'business_name' => $app['business_name'],
                'phone' => $app['phone'],
                'city' => $app['city'],
                'ntn' => $app['ntn'],
                'shop_type' => $app['shop_type'],
                'monthly_volume' => $app['monthly_volume'],
                'is_active' => 1,
                'application_id' => $app['id'],
            ]);
            Application::markApproved((int)$app['id'], (int)$admin['id'], $userId);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            app_log('admin.approve', ['error' => $e->getMessage()]);
            flash('error', 'Could not approve the application.');
            return $this->redirect('admin/applications/' . $app['id']);
        }
        flash('success', 'Dealer account created for ' . $app['email'] . '.');
        return $this->redirect('admin/applications/' . $app['id']);
    }

    public function reject(Request $request, array $params): Response
    {
        $app = Application::find((int)$params['id']);
        if (!$app) {
            throw new HttpException(404, 'Application not found');
        }
        $admin = current_user();
        Application::reject((int)$app['id'], (int)$admin['id']);
        flash('success', 'Application rejected.');
        return $this->redirect('admin/applications/' . $app['id']);
    }
}
