<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContactMessage;

final class MessageController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminPage('admin/messages/index', [
            'title' => 'Messages — Admin',
            'page' => 'messages',
            'messages' => ContactMessage::all(),
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $msg = ContactMessage::find((int)$params['id']);
        if (!$msg) {
            throw new HttpException(404, 'Message not found');
        }
        ContactMessage::markRead((int)$msg['id']);
        $msg['is_read'] = 1;
        return $this->adminPage('admin/messages/show', [
            'title' => 'Message from ' . $msg['business_name'],
            'page' => 'messages',
            'message' => $msg,
        ]);
    }
}
