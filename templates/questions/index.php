<?php
$items = $result['items'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h1 class="mb-1">Questions</h1>
    <p class="text-body-secondary mb-0"><?= (int) $result['total'] ?> question(s)</p>
  </div>
  <a class="btn btn-accent" href="/questions/new">Poser une question</a>
</div>

<form method="get" action="/questions" class="card card-body mb-4" role="search">
  <div class="row g-2 align-items-end">
    <div class="col-md-6">
      <label for="q" class="form-label">Mot-clé</label>
      <input type="search" class="form-control" id="q" name="q" value="<?= e($keyword) ?>" maxlength="100" placeholder="Ex. : session PHP">
    </div>
    <div class="col-md-4">
      <label for="tag" class="form-label">Tag</label>
      <select class="form-select" id="tag" name="tag">
        <option value="">Tous les tags</option>
        <?php foreach ($catalogue as $skill): ?>
          <option value="<?= (int) $skill['id'] ?>" <?= (int) $skill['id'] === $tag ? 'selected' : '' ?>><?= e($skill['nom']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-afridev">Rechercher</button>
    </div>
  </div>
</form>

<?php if ($items === []): ?>
  <div class="text-center text-body-secondary py-5">
    <p class="mb-0">Aucune question ne correspond à votre recherche.</p>
  </div>
<?php else: ?>
  <div class="d-flex flex-column gap-3">
    <?php foreach ($items as $item): ?>
      <article class="card card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2">
          <h2 class="h5 mb-1"><a class="text-reset text-decoration-none" href="/questions/<?= (int) $item['id'] ?>"><?= e($item['titre']) ?></a></h2>
          <div class="d-flex gap-2 align-items-start">
            <?php if ($item['statut'] === 'resolue'): ?><span class="badge badge-dispo">Résolue</span><?php endif; ?>
            <span class="badge badge-niveau"><?= (int) $item['nb_reponses'] ?> réponse(s)</span>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-1 my-2">
          <?php foreach ($item['tags'] as $t): ?>
            <a class="tag" href="<?= e(url_with('/questions', ['tag' => $t['id']])) ?>"><?= e($t['nom']) ?></a>
          <?php endforeach; ?>
        </div>
        <div class="small text-body-secondary">
          <a href="/users/<?= (int) $item['user_id'] ?>"><?= e($item['prenom'] . ' ' . $item['nom']) ?></a> · <?= e(date_fr($item['created_at'])) ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
$path = '/questions';
$params = ['q' => $keyword, 'tag' => $tag];
$page = $result['page'];
$pages = $result['pages'];
require __DIR__ . '/../partials/pagination.php';
?>
