<?php
$p = $product ?? null;
$isEdit = (bool)$p;
$old = \App\Core\Session::get('_old', []) ?: [];
\App\Core\Session::forget('_old');
$val = static function (string $key, $fallback = '') use ($old, $p) {
    if (array_key_exists($key, $old)) {
        return $old[$key];
    }
    if ($p && array_key_exists($key, $p) && $p[$key] !== null) {
        return $p[$key];
    }
    return $fallback;
};
$colorsPalette = $colors ?? [];
$selectedColors = $old['colors'] ?? ($p['colors'] ?? []);
if (!is_array($selectedColors)) {
    $selectedColors = [];
}
$sizes = $old['sizes'] ?? (isset($p['sizes']) ? implode(', ', $p['sizes']) : '');
$tiers = $p['tiers'] ?? [['min' => (int)$val('moq', 12), 'max' => '', 'discount' => 0]];
if (!empty($old['tier_min']) && is_array($old['tier_min'])) {
    $tiers = [];
    foreach ($old['tier_min'] as $i => $min) {
        if ($min === '' || $min === null) {
            continue;
        }
        $tiers[] = ['min' => $min, 'max' => $old['tier_max'][$i] ?? '', 'discount' => $old['tier_discount'][$i] ?? 0];
    }
}
$images = $p['images'] ?? [];
if (!empty($old['image_urls']) && is_array($old['image_urls'])) {
    $images = $old['image_urls'];
}
$action = $isEdit ? url('admin/products/' . $p['id']) : url('admin/products');
?>
<div class="admin-head">
  <div>
    <div class="kicker">Catalogue</div>
    <h1><?= $isEdit ? 'Edit style' : 'New product' ?></h1>
    <p><?= $isEdit ? e($p['sku'] . ' · ' . $p['name']) : 'Set the MOQ, tiers, images and assortment.' ?></p>
  </div>
  <?php if ($isEdit): ?>
    <a class="btn btn--ghost" href="<?= e(url('product/' . $p['slug'])) ?>" target="_blank" rel="noopener">View on site</a>
  <?php endif; ?>
</div>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" id="productForm" class="form-grid" novalidate>
  <?= csrf_field() ?>
  <div class="panel">
    <h2>Details</h2>
    <div class="field-row">
      <label class="field">Name <span class="req">*</span><input type="text" name="name" required value="<?= e((string)$val('name')) ?>"></label>
      <label class="field">SKU <span class="req">*</span><input type="text" name="sku" required value="<?= e((string)$val('sku')) ?>"></label>
    </div>
    <label class="field">Slug<input type="text" name="slug" value="<?= e((string)$val('slug')) ?>"><span class="help">Leave blank to generate from the name.</span></label>
    <div class="field-row">
      <label class="field">Category
        <select name="category_id" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>"<?= (int)$val('category_id', $c['id'] ?? 0) === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field">Age group
        <select name="age_group_id" required>
          <?php foreach ($ages as $a): ?>
            <option value="<?= (int)$a['id'] ?>"<?= (int)$val('age_group_id', $a['id'] ?? 0) === (int)$a['id'] ? ' selected' : '' ?>><?= e($a['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <div class="field-row">
      <label class="field">Season
        <select name="season">
          <?php foreach (($seasons ?: []) as $s): $sk = is_array($s) ? ($s['key'] ?? '') : $s; $sl = is_array($s) ? ($s['label'] ?? $sk) : $s; ?>
            <option value="<?= e($sk) ?>"<?= (string)$val('season', 'core') === (string)$sk ? ' selected' : '' ?>><?= e($sl) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field">Stock status
        <select name="stock_status">
          <?php foreach (['in_stock' => 'In stock', 'limited' => 'Limited', 'made_to_order' => 'Made to order'] as $k => $lab): ?>
            <option value="<?= e($k) ?>"<?= (string)$val('stock_status', 'in_stock') === $k ? ' selected' : '' ?>><?= e($lab) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <div class="field-row">
      <label class="field">Fabric<input type="text" name="fabric" value="<?= e((string)$val('fabric')) ?>"></label>
      <label class="field">GSM<input type="number" name="gsm" min="0" value="<?= e((string)$val('gsm')) ?>"></label>
    </div>
    <div class="field-row">
      <label class="field">Lead time (days)<input type="number" name="lead_time_days" min="1" value="<?= e((string)$val('lead_time_days', 7)) ?>"></label>
      <label class="field">Tag<input type="text" name="tag" value="<?= e((string)$val('tag')) ?>" placeholder="Bestseller, New…"></label>
    </div>
    <div class="field-row">
      <label class="field">Pack units<input type="number" name="pack_units" min="0" value="<?= e((string)$val('pack_units')) ?>"></label>
      <label class="field">Pack ratio<input type="text" name="pack_ratio" value="<?= e((string)$val('pack_ratio')) ?>" placeholder="e.g. 1-2-2-1"></label>
    </div>
    <label class="field">Description<textarea name="description" rows="5"><?= e((string)$val('description')) ?></textarea></label>
    <label class="field" style="flex-direction:row;align-items:center;gap:8px">
      <input type="checkbox" name="is_active" value="1"<?= $val('is_active', 1) ? ' checked' : '' ?>> Active in the catalogue
    </label>
  </div>

  <div>
    <div class="panel" style="margin-bottom:14px">
      <h2>Images</h2>
      <div class="img-preview" id="imagePreview">
        <?php foreach ($images as $url): if (!$url) continue; ?>
          <figure>
            <img src="<?= e($url) ?>" alt="">
            <input type="hidden" name="image_urls[]" value="<?= e($url) ?>">
            <button type="button" data-remove-img aria-label="Remove">×</button>
          </figure>
        <?php endforeach; ?>
      </div>
      <label class="field">Upload an image
        <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp,image/gif">
        <span class="help">JPG, PNG, WEBP or GIF, up to 4 MB. Existing image URLs are kept unless you remove them.</span>
      </label>
      <label class="field">Or paste an image URL
        <input type="url" id="imageUrlInput" placeholder="https://…">
      </label>
      <button type="button" class="btn btn--ghost btn--sm" id="addImageUrl">Add URL</button>
    </div>

    <div class="panel" style="margin-bottom:14px">
      <h2>MOQ &amp; price tiers</h2>
      <div class="field-row">
        <label class="field">MOQ (pieces) <span class="req">*</span>
          <input type="number" name="moq" id="moqInput" min="1" required value="<?= e((string)$val('moq', 12)) ?>">
        </label>
        <label class="field">Base price (Rs.) <span class="req">*</span>
          <input type="number" name="base_price" id="basePriceInput" min="1" step="0.01" required value="<?= e((string)$val('base_price')) ?>">
        </label>
      </div>
      <p class="help">The first tier must start at the product MOQ. Discounts apply to the base wholesale price.</p>
      <div class="tier-editor" id="tierEditor">
        <table>
          <thead><tr><th>Min qty</th><th>Max qty</th><th>Discount %</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($tiers as $t): ?>
              <tr>
                <td><input type="number" name="tier_min[]" min="1" value="<?= e((string)$t['min']) ?>" required></td>
                <td><input type="number" name="tier_max[]" min="1" value="<?= e((string)($t['max'] ?? '')) ?>" placeholder="Open"></td>
                <td><input type="number" name="tier_discount[]" min="0" max="90" step="0.01" value="<?= e((string)$t['discount']) ?>"></td>
                <td><button type="button" class="btn btn--ghost btn--sm" data-remove-tier>Remove</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p class="tier-msg" id="tierMsg"></p>
        <button type="button" class="btn btn--ghost btn--sm" id="addTier">Add tier</button>
      </div>
    </div>

    <div class="panel">
      <h2>Assortment</h2>
      <label class="field">Sizes
        <input type="hidden" name="sizes" id="sizesInput" value="<?= e(is_array($sizes) ? implode(', ', $sizes) : (string)$sizes) ?>">
        <div class="chips" id="sizeChips"></div>
        <span class="chip-add"><input type="text" id="sizeAdd" placeholder="Add a size and press Enter"></span>
        <span class="help">Example: 4-5y, 6-7y, 8-9y</span>
      </label>
      <div class="field">Colours
        <div class="swatch-pick" id="colorPick">
          <?php foreach ($colorsPalette as $key => $meta):
              $on = in_array($key, $selectedColors, true);
          ?>
            <label class="<?= $on ? 'is-on' : '' ?>">
              <input type="checkbox" name="colors[]" value="<?= e($key) ?>"<?= $on ? ' checked' : '' ?>>
              <i style="background:<?= e($meta['h'] ?? $meta['hex'] ?? '#ccc') ?>"></i>
              <?= e($meta['n'] ?? $meta['name'] ?? $key) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap">
      <button class="btn btn--sun" type="submit">Save product</button>
      <a class="btn btn--ghost" href="<?= e(url('admin/products')) ?>">Cancel</a>
      <?php if ($isEdit): ?>
        <button class="btn btn--danger" type="submit" form="deleteProduct" data-confirm="Remove this style from the catalogue? This cannot be undone.">Delete</button>
      <?php endif; ?>
    </div>
  </div>
</form>
<?php if ($isEdit): ?>
  <form id="deleteProduct" method="post" action="<?= e(url('admin/products/' . $p['id'] . '/delete')) ?>"><?= csrf_field() ?></form>
<?php endif; ?>
