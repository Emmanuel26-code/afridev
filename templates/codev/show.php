<?php
use App\Models\Need;
use App\Services\ProfileService;

$requises = array_filter($need['skills'], static fn ($s) => $s['type'] === 'requise');
$souhaitees = array_filter($need['skills'], static fn ($s) => $s['type'] === 'souhaitee');
?>
<nav aria-label="Fil d'Ariane" class="mb-3"><a href="/codev">← Tous les besoins</a></nav>

<article class="card card-body p-4">
  <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
    <h1 class="h3 mb-0"><?= e($need['titre']) ?></h1>
    <span class="badge badge-niveau align-self-start"><?= e(Need::STATUTS[$need['statut']]) ?></span>
  </div>
  <div class="small text-body-secondary mb-3">
    <a href="/users/<?= (int) $need['user_id'] ?>"><?= e($need['prenom'] . ' ' . $need['nom']) ?></a>
    · <?= e(date_fr($need['created_at'])) ?>
    <?php if ($need['projet_titre']): ?>
      · projet <a href="/projects/<?= (int) $need['project_id'] ?>"><?= e($need['projet_titre']) ?></a>
    <?php endif; ?>
  </div>
  <div class="pre-line mb-4"><?= e($need['description']) ?></div>

  <dl class="row mb-0">
    <dt class="col-sm-4">Rôle recherché</dt>
    <dd class="col-sm-8"><?= e($need['role_recherche']) ?></dd>
    <dt class="col-sm-4">Niveau minimum</dt>
    <dd class="col-sm-8"><?= e(ProfileService::NIVEAUX[(int) $need['niveau_min']] ?? '') ?></dd>
    <dt class="col-sm-4">Disponibilité</dt>
    <dd class="col-sm-8"><?= e(Need::DISPONIBILITES[$need['disponibilite']]) ?></dd>
    <dt class="col-sm-4">Compétences requises</dt>
    <dd class="col-sm-8 d-flex flex-wrap gap-1">
      <?php foreach ($requises as $s): ?><span class="tag tag-required"><?= e($s['nom']) ?></span><?php endforeach; ?>
    </dd>
    <?php if ($souhaitees): ?>
      <dt class="col-sm-4">Compétences souhaitées</dt>
      <dd class="col-sm-8 d-flex flex-wrap gap-1">
        <?php foreach ($souhaitees as $s): ?><span class="tag"><?= e($s['nom']) ?></span><?php endforeach; ?>
      </dd>
    <?php endif; ?>
  </dl>
</article>

<?php if ($isOwner): ?>
  <div class="card card-body mt-3 text-body-secondary">
    Les développeurs recommandés par CODEV s'afficheront ici (étape suivante du projet).
  </div>
<?php endif; ?>
