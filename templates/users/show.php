<?php
use App\Services\ProfileService;

$statuts = ['idee' => 'Idée', 'en_cours' => 'En cours', 'termine' => 'Terminé'];
$counts = $activity['counts'];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
  <div class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($profile['pseudo'], 0, 1))) ?></div>
  <div class="flex-grow-1">
    <h1 class="h3 mb-0"><?= e($profile['nom']) ?></h1>
    <div class="text-body-secondary">@<?= e($profile['pseudo']) ?><?= $profile['pays_ville'] ? ' · ' . e($profile['pays_ville']) : '' ?></div>
    <span class="badge <?= $profile['disponibilite'] === 'disponible' ? 'text-bg-success' : 'text-bg-secondary' ?>">
      <?= e(ProfileService::DISPONIBILITES[$profile['disponibilite']]) ?>
    </span>
    <?php if ($profile['github_url']): ?>
      <a class="ms-2 small" href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener noreferrer nofollow">GitHub</a>
    <?php endif; ?>
  </div>
  <?php if ($isMe): ?>
    <a class="btn btn-outline-secondary btn-sm" href="/users/me">Modifier mon profil</a>
  <?php endif; ?>
</div>

<?php if ($profile['bio']): ?>
  <p class="mb-4 pre-line"><?= e($profile['bio']) ?></p>
<?php endif; ?>

<div class="row g-4">
  <section class="col-md-6">
    <h2 class="h5">Compétences</h2>
    <?php if ($skills): ?>
      <ul class="list-group">
        <?php foreach ($skills as $skill): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?= e($skill['nom']) ?>
              <?php if ($skill['annees_experience'] !== null): ?>
                <small class="text-body-secondary">· <?= (int) $skill['annees_experience'] ?> an(s)</small>
              <?php endif; ?>
            </span>
            <span class="badge text-bg-light border"><?= e(ProfileService::NIVEAUX[(int) $skill['niveau']]) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-body-secondary">Aucune compétence renseignée.
        <?php if ($isMe): ?><a href="/users/me/skills">Ajouter mes compétences</a><?php endif; ?></p>
    <?php endif; ?>
  </section>

  <section class="col-md-6">
    <h2 class="h5">Activité</h2>
    <p><?= (int) $counts['questions'] ?> question(s) · <?= (int) $counts['reponses'] ?> réponse(s)
      dont <?= (int) $counts['acceptees'] ?> acceptée(s)</p>

    <h2 class="h5 mt-4">Projets</h2>
    <?php if ($activity['projects']): ?>
      <ul class="list-unstyled">
        <?php foreach ($activity['projects'] as $project): ?>
          <li><?= e($project['titre']) ?> <span class="badge text-bg-light border"><?= e($statuts[$project['statut']]) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-body-secondary">Aucun projet publié.</p>
    <?php endif; ?>

    <h2 class="h5 mt-4">Équipes</h2>
    <?php if ($activity['teams']): ?>
      <ul class="list-unstyled">
        <?php foreach ($activity['teams'] as $team): ?>
          <li><?= e($team['nom']) ?> <small class="text-body-secondary">(<?= e($team['role']) ?>)</small></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-body-secondary">Membre d'aucune équipe.</p>
    <?php endif; ?>
  </section>
</div>
