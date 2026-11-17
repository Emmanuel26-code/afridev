<?php
use App\Core\Csrf;
use App\Models\Project;
?>
<nav aria-label="Fil d'Ariane" class="mb-3"><a href="/projects">← Tous les projets</a></nav>

<article class="card card-body p-4">
  <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
    <h1 class="h3 mb-0"><?= e($project['titre']) ?></h1>
    <span class="badge badge-statut-<?= e($project['statut']) ?> align-self-start"><?= e(Project::STATUTS[$project['statut']]) ?></span>
  </div>
  <div class="small text-body-secondary mb-3">
    <a href="/users/<?= (int) $project['user_id'] ?>"><?= e($project['prenom'] . ' ' . $project['nom']) ?></a>
    · <?= e(date_fr($project['created_at'])) ?>
  </div>
  <div class="pre-line mb-3"><?= e($project['description']) ?></div>
  <div class="d-flex flex-wrap gap-1 mb-3">
    <?php foreach ($project['skills'] as $s): ?>
      <a class="tag" href="<?= e(url_with('/projects', ['tech' => $s['id']])) ?>"><?= e($s['nom']) ?></a>
    <?php endforeach; ?>
  </div>
  <?php if ($project['lien_git'] || $project['lien_demo']): ?>
    <div class="d-flex flex-wrap gap-2">
      <?php if ($project['lien_git']): ?>
        <a class="btn btn-ink btn-sm" href="<?= e($project['lien_git']) ?>" target="_blank" rel="noopener noreferrer nofollow">Code source</a>
      <?php endif; ?>
      <?php if ($project['lien_demo']): ?>
        <a class="btn btn-accent btn-sm" href="<?= e($project['lien_demo']) ?>" target="_blank" rel="noopener noreferrer nofollow">Voir la démo</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</article>

<?php if ($isOwner): ?>
  <div class="d-flex gap-2 mt-3">
    <a class="btn btn-outline-secondary btn-sm" href="/projects/<?= (int) $project['id'] ?>/edit">Modifier</a>
    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirm-delete">Supprimer</button>
  </div>

  <div class="modal fade" id="confirm-delete" tabindex="-1" aria-labelledby="confirm-delete-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title h5" id="confirm-delete-title">Supprimer ce projet ?</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">Cette action est définitive.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <form method="post" action="/projects/<?= (int) $project['id'] ?>/delete">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-danger">Supprimer</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
