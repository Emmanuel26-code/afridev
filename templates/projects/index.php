<?php
use App\Models\Project;

$items = $result['items'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h1 class="mb-1">Projets</h1>
    <p class="text-body-secondary mb-0"><?= (int) $result['total'] ?> projet(s)</p>
  </div>
  <a class="btn btn-accent" href="/projects/new">Publier un projet</a>
</div>

<form method="get" action="/projects" class="card card-body mb-4" role="search">
  <div class="row g-2 align-items-end">
    <div class="col-md-5">
      <label for="q" class="form-label">Mot-clé</label>
      <input type="search" class="form-control" id="q" name="q" value="<?= e($keyword) ?>" maxlength="100" placeholder="Ex. : application mobile">
    </div>
    <div class="col-md-3">
      <label for="tech" class="form-label">Technologie</label>
      <select class="form-select" id="tech" name="tech">
        <option value="">Toutes</option>
        <?php foreach ($catalogue as $skill): ?>
          <option value="<?= (int) $skill['id'] ?>" <?= (int) $skill['id'] === $tech ? 'selected' : '' ?>><?= e($skill['nom']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label for="statut" class="form-label">Statut</label>
      <select class="form-select" id="statut" name="statut">
        <option value="">Tous</option>
        <?php foreach (Project::STATUTS as $value => $label): ?>
          <option value="<?= e($value) ?>" <?= $value === $statut ? 'selected' : '' ?>><?= e($label) ?></option>
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
    <p class="mb-0">Aucun projet ne correspond à votre recherche.</p>
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($items as $item): ?>
      <div class="col-md-6">
        <article class="card card-body h-100">
          <div class="d-flex justify-content-between gap-2 mb-1">
            <h2 class="h5 mb-0"><a class="text-reset text-decoration-none" href="/projects/<?= (int) $item['id'] ?>"><?= e($item['titre']) ?></a></h2>
            <span class="badge badge-statut-<?= e($item['statut']) ?> align-self-start"><?= e(Project::STATUTS[$item['statut']]) ?></span>
          </div>
          <p class="text-body-secondary mb-2 clamp-3"><?= e($item['description']) ?></p>
          <div class="d-flex flex-wrap gap-1 mb-2">
            <?php foreach ($item['skills'] as $s): ?>
              <a class="tag" href="<?= e(url_with('/projects', ['tech' => $s['id']])) ?>"><?= e($s['nom']) ?></a>
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
$path = '/projects';
$params = ['q' => $keyword, 'tech' => $tech, 'statut' => $statut];
$page = $result['page'];
$pages = $result['pages'];
require __DIR__ . '/../partials/pagination.php';
?>
