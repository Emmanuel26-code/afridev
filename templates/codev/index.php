<?php $items = $result['items']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h1 class="mb-1">CODEV</h1>
    <p class="text-body-secondary mb-0">Décrivez votre besoin : l'IA recommande les développeurs les plus adaptés. <?= (int) $result['total'] ?> besoin(s) ouvert(s).</p>
  </div>
  <a class="btn btn-accent" href="/codev/needs/new">Publier un besoin</a>
</div>

<?php if ($items === []): ?>
  <div class="text-center text-body-secondary py-5"><p class="mb-0">Aucun besoin ouvert pour le moment.</p></div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($items as $item): ?>
      <div class="col-md-6">
        <article class="card card-body h-100">
          <h2 class="h5 mb-1"><a class="text-reset text-decoration-none" href="/codev/needs/<?= (int) $item['id'] ?>"><?= e($item['titre']) ?></a></h2>
          <div class="fw-semibold text-accent small mb-2"><?= e($item['role_recherche']) ?></div>
          <div class="d-flex flex-wrap gap-1 mb-2">
            <?php foreach ($item['skills'] as $s): ?>
              <span class="tag<?= $s['type'] === 'requise' ? ' tag-required' : '' ?>"><?= e($s['nom']) ?></span>
            <?php endforeach; ?>
          </div>
          <div class="small text-body-secondary mt-auto">
            <a href="/users/<?= (int) $item['user_id'] ?>"><?= e($item['prenom'] . ' ' . $item['nom']) ?></a> · <?= e(date_fr($item['created_at'])) ?>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
$path = '/codev';
$params = [];
$page = $result['page'];
$pages = $result['pages'];
require __DIR__ . '/../partials/pagination.php';
?>
