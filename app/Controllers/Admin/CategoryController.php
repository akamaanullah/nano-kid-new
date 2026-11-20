<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;

final class CategoryController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminPage('admin/categories/index', [
            'title' => 'Categories — Admin',
            'page' => 'categories',
            'categories' => Category::all(false),
        ]);
    }

    public function store(Request $request, array $params): Response
    {
        $label = $request->string('label');
        $slug = slugify($request->string('slug') ?: $label);
        if ($label === '' || $slug === '') {
            flash('error', 'Label is required.');
            return $this->redirect('admin/categories');
        }
        if (Category::findBySlug($slug)) {
            flash('error', 'That slug is already used.');
            return $this->redirect('admin/categories');
        }
        Category::create([
            'label' => $label,
            'slug' => $slug,
            'image_url' => $request->string('image_url') ?: null,
            'sort_order' => $request->int('sort_order', 0),
            'is_active' => $request->input('is_active') ? 1 : 0,
        ]);
        flash('success', 'Category added.');
        return $this->redirect('admin/categories');
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int)$params['id'];
        $row = Category::find($id);
        if (!$row) {
            flash('error', 'Category not found.');
            return $this->redirect('admin/categories');
        }
        Category::updateRow($id, [
            'label' => $request->string('label') ?: $row['label'],
            'slug' => slugify($request->string('slug') ?: $row['slug']),
            'image_url' => $request->string('image_url') ?: $row['image_url'],
            'sort_order' => $request->int('sort_order', (int)$row['sort_order']),
            'is_active' => $request->input('is_active') ? 1 : 0,
        ]);
        flash('success', 'Category updated.');
        return $this->redirect('admin/categories');
    }
}
