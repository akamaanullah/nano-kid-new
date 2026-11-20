<?php $categories = $categories ?? []; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Catalogue</div>
    <h1>Categories</h1>
    <p>Used on the storefront filters, mega menu and product cards.</p>
  </div>
</div>
<div class="form-grid">
  <div class="panel">
    <?php if (!$categories): ?>
      <div class="empty"><h3>No categories</h3><p>Add the first category to start grouping styles.</p></div>
    <?php else: ?>
      <?php foreach ($categories as $c): ?>
        <form method="post" action="<?= e(url('admin/categories/' . $c['id'])) ?>" style="display:grid;grid-template-columns:1.4fr 1fr 80px auto auto;gap:8px;align-items:end;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)">
          <?= csrf_field() ?>
          <label class="field" style="margin:0">Label<input type="text" name="label" value="<?= e($c['label']) ?>" required></label>
          <label class="field" style="margin:0">Slug<input type="text" name="slug" value="<?= e($c['slug']) ?>" required></label>
          <label class="field" style="margin:0">Order<input type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>"></label>
          <label class="field" style="margin:0;flex-direction:row;align-items:center;gap:6px;padding-bottom:8px"><input type="checkbox" name="is_active" value="1"<?= $c['is_active'] ? ' checked' : '' ?>> Active</label>
          <input type="hidden" name="image_url" value="<?= e((string)$c['image_url']) ?>">
          <button class="btn btn--sm" type="submit">Save</button>
        </form>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="panel">
    <h2>Add category</h2>
    <form method="post" action="<?= e(url('admin/categories')) ?>">
      <?= csrf_field() ?>
      <label class="field">Label<input type="text" name="label" required></label>
      <label class="field">Slug<input type="text" name="slug" placeholder="Generated if left blank"></label>
      <label class="field">Image URL<input type="url" name="image_url"></label>
      <label class="field">Sort order<input type="number" name="sort_order" value="0"></label>
      <label class="field" style="flex-direction:row;align-items:center;gap:8px"><input type="checkbox" name="is_active" value="1" checked> Active</label>
      <button class="btn btn--sun" type="submit">Add category</button>
    </form>
  </div>
</div>
