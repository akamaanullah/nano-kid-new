<?php
$errorStatus = $errorStatus ?? 500;
$errorMessage = $errorMessage ?? 'Something went wrong.';
?>
<section class="section">
  <div class="wrap" style="max-width:40rem">
    <span class="eyebrow"><?= (int)$errorStatus ?></span>
    <h1><?= (int)$errorStatus === 404 ? 'Page not <em>found.</em>' : 'Please <em>try again.</em>' ?></h1>
    <p class="lead"><?= e((string)$errorMessage) ?></p>
    <p><a class="btn btn--pill" href="<?= e(url('/')) ?>">Back to the homepage</a></p>
  </div>
</section>
