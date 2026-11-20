<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\PrivateLabel;

final class PrivateLabelController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminPage('admin/private-label/index', [
            'title' => 'Private label — Admin',
            'page' => 'private-label',
            'requests' => PrivateLabel::all(),
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $row = PrivateLabel::find((int)$params['id']);
        if (!$row) {
            throw new HttpException(404, 'Request not found');
        }
        return $this->adminPage('admin/private-label/show', [
            'title' => 'Private label #' . $row['id'],
            'page' => 'private-label',
            'row' => $row,
        ]);
    }

    public function status(Request $request, array $params): Response
    {
        $row = PrivateLabel::find((int)$params['id']);
        if (!$row) {
            throw new HttpException(404, 'Request not found');
        }
        $status = $request->string('status');
        $allowed = ['new', 'reviewing', 'quoted', 'accepted', 'declined'];
        if (!in_array($status, $allowed, true)) {
            flash('error', 'Invalid status.');
            return $this->redirect('admin/private-label/' . $row['id']);
        }
        PrivateLabel::setStatus((int)$row['id'], $status);
        flash('success', 'Status updated.');
        return $this->redirect('admin/private-label/' . $row['id']);
    }
}
