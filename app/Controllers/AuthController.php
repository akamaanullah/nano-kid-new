<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Application;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginForm(Request $request, array $params): Response
    {
        return $this->page('auth/login', [
            'title' => 'Dealer sign in — Nano Kids Wholesale',
            'description' => 'Sign in to your Nano Kids wholesale dashboard.',
            'page' => 'login',
            'boot' => store_boot(),
        ]);
    }

    public function login(Request $request, array $params): Response
    {
        $email = mb_strtolower($request->string('email'));
        $password = (string)$request->input('password', '');
        $user = $email !== '' ? User::findByEmail($email) : null;
        $ok = $user && password_verify($password, (string)$user['password_hash']);
        if (!$ok) {
            // Mitigate timing a little
            password_verify($password, '$2y$12$C6UzMDMFkbkGQKq2M5nPDeqKQH0xM8k5yG9nG1qkQmZp3fS2vO2uW');
            if ($request->wantsJson()) {
                return $this->json(['ok' => false, 'errors' => ['Those details do not match a trade account.']], 422);
            }
            flash('error', 'Those details do not match a trade account.');
            flash('_old', ['email' => $email]);
            return $this->redirect($this->loginPath($request));
        }
        if (empty($user['is_active'])) {
            flash('error', 'This account is paused. Please contact the trade desk.');
            return $this->redirect($this->loginPath($request));
        }
        Session::regenerate();
        Session::set('user_id', (int)$user['id']);
        User::touchLogin((int)$user['id']);
        $GLOBALS['nk_auth_user'] = $user;

        $intended = Session::get('_intended');
        Session::forget('_intended');
        if (($user['role'] ?? '') === 'admin') {
            $to = is_string($intended) && str_starts_with((string)$intended, '/admin') ? ltrim((string)$intended, '/') : 'admin';
        } else {
            $to = is_string($intended) && $intended !== '' && $intended !== '/login' ? ltrim((string)$intended, '/') : 'dashboard';
        }
        if ($request->wantsJson()) {
            return $this->json(['ok' => true, 'redirect' => url($to), 'role' => $user['role']]);
        }
        return $this->redirect($to);
    }

    public function logout(Request $request, array $params): Response
    {
        Session::destroy();
        Session::start();
        flash('success', 'You have been signed out.');
        $fromAdmin = str_starts_with($request->path(), '/admin');
        return $this->redirect($fromAdmin ? 'admin/login' : 'login');
    }

    public function applyForm(Request $request, array $params): Response
    {
        return $this->page('auth/apply', [
            'title' => 'Become a dealer — Nano Kids Wholesale',
            'description' => 'Apply for a Nano Kids wholesale dealership. Trade pricing, NTN invoicing and credit for approved retailers.',
            'page' => 'apply',
            'boot' => store_boot(),
        ]);
    }

    public function applySubmit(Request $request, array $params): Response
    {
        $data = [
            'business_name' => $request->string('business'),
            'contact_name' => $request->string('contact'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'ntn' => $request->string('ntn'),
            'city' => $request->string('city'),
            'shop_type' => $request->string('type'),
            'monthly_volume' => $request->string('volume'),
            'notes' => $request->string('notes'),
        ];
        $errors = [];
        foreach (['business_name', 'contact_name', 'phone', 'ntn', 'city', 'shop_type', 'monthly_volume'] as $k) {
            if ($data[$k] === '') {
                $errors[] = 'Please complete every required field.';
                break;
            }
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid work email.';
        }
        if ($errors) {
            if ($request->wantsJson()) {
                return $this->json(['ok' => false, 'errors' => $errors], 422);
            }
            flash('error', implode(' ', $errors));
            flash('_old', $request->all());
            return $this->redirect('apply');
        }
        $existing = User::findByEmail($data['email']);
        if ($existing) {
            $msg = 'An account already exists for this email. Please sign in.';
            if ($request->wantsJson()) {
                return $this->json(['ok' => false, 'errors' => [$msg]], 422);
            }
            flash('error', $msg);
            return $this->redirect('login');
        }
        $id = Application::create($data);
        if ($request->wantsJson()) {
            return $this->json(['ok' => true, 'id' => $id, 'message' => 'Application received.']);
        }
        flash('success', 'Application received. The desk will review it shortly.');
        return $this->redirect('apply?sent=1');
    }

    public function dashboard(Request $request, array $params): Response
    {
        $user = current_user();
        if (!$user) {
            return $this->redirect('login');
        }
        if (($user['role'] ?? '') === 'admin') {
            return $this->redirect('admin');
        }
        $quotes = Quote::forUser((int)$user['id']);
        $orders = Order::forUser((int)$user['id']);
        return $this->page('auth/dashboard', [
            'title' => 'Dealer dashboard — Nano Kids Wholesale',
            'description' => 'Your quotes, orders and account.',
            'page' => 'dashboard',
            'quotes' => $quotes,
            'orders' => $orders,
            'user' => $user,
            'boot' => store_boot(),
        ]);
    }

    public function adminLoginForm(Request $request, array $params): Response
    {
        return $this->view('admin/login', [
            'title' => 'Admin sign in — Nano Kids Wholesale',
            'error' => flash('error'),
        ]);
    }

    private function loginPath(Request $request): string
    {
        return str_starts_with($request->path(), '/admin') ? 'admin/login' : 'login';
    }
}
