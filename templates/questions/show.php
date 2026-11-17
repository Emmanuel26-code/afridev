<?php
use App\Core\Csrf;

$isAuthor = $me !== null && (int) $question['user_id'] === $me;
?>
<nav aria-label="Fil d'Ariane" class="mb-3"><a href="/questions">← Toutes les questions</a></nav>

<article class="card card-body p-4 mb-4">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <h1 class="h3 mb-2"><?= e($question['titre']) ?></h1>
    <?php if ($question['statut'] === 'resolue'): ?><span class="badge badge-dispo align-self-start">Résolue</span><?php endif; ?>
  </div>
  <div class="small text-body-secondary mb-3">
    <a href="/users/<?= (int) $question['user_id'] ?>"><?= e($question['prenom'] . ' ' . $question['nom']) ?></a>
    · <?= e(date_fr($question['created_at'])) ?>
  </div>
  <div class="pre-line mb-3"><?= e($question['contenu']) ?></div>
  <div class="d-flex flex-wrap gap-1">
    <?php foreach ($question['tags'] as $t): ?>
      <a class="tag" href="<?= e(url_with('/questions', ['tag' => $t['id']])) ?>"><?= e($t['nom']) ?></a>
    <?php endforeach; ?>
  </div>
</article>

<h2 class="h4 mb-3" id="reponses"><?= count($answers) ?> réponse(s)</h2>

<?php foreach ($answers as $answer):
    $mine = $me !== null && (int) $answer['user_id'] === $me; ?>
  <article class="card card-body mb-3<?= $answer['est_acceptee'] ? ' answer-accepted' : '' ?>" id="reponse-<?= (int) $answer['id'] ?>">
    <?php if ($answer['est_acceptee']): ?>
      <div class="small fw-semibold text-accent mb-2">✓ Réponse acceptée</div>
    <?php endif; ?>
    <div class="pre-line mb-3"><?= e($answer['contenu']) ?></div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="small text-body-secondary">
        <a href="/users/<?= (int) $answer['user_id'] ?>"><?= e($answer['prenom'] . ' ' . $answer['nom']) ?></a>
        · <?= e(date_fr($answer['created_at'])) ?>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <?php if ($me !== null && !$mine): ?>
          <form method="post" action="/answers/<?= (int) $answer['id'] ?>/vote">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-sm <?= $answer['a_vote'] ? 'btn-afridev' : 'btn-outline-secondary' ?>"
                    aria-pressed="<?= $answer['a_vote'] ? 'true' : 'false' ?>">▲ <?= (int) $answer['votes'] ?></button>
          </form>
        <?php else: ?>
          <span class="badge badge-niveau">▲ <?= (int) $answer['votes'] ?> vote(s)</span>
        <?php endif; ?>
        <?php if ($isAuthor && !$answer['est_acceptee']): ?>
          <form method="post" action="/answers/<?= (int) $answer['id'] ?>/accept">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Accepter</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </article>
<?php endforeach; ?>

<section class="card card-body p-4 mt-4">
  <h2 class="h5">Votre réponse</h2>
  <?php if ($me === null): ?>
    <p class="mb-0"><a href="/auth/login">Connectez-vous</a> pour répondre.</p>
  <?php else: ?>
    <form method="post" action="/questions/<?= (int) $question['id'] ?>/answers" novalidate>
      <?= Csrf::field() ?>
      <label for="contenu" class="visually-hidden">Votre réponse</label>
      <textarea class="form-control<?= $error ? ' is-invalid' : '' ?> mb-2" id="contenu" name="contenu" rows="5"
                required maxlength="10000"><?= e($draft) ?></textarea>
      <?php if ($error): ?><div class="invalid-feedback d-block mb-2"><?= e($error) ?></div><?php endif; ?>
      <button type="submit" class="btn btn-afridev">Publier ma réponse</button>
    </form>
  <?php endif; ?>
</section>
