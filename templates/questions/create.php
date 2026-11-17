<?php use App\Core\Csrf; ?>
<div class="mx-auto form-wide">
  <h1 class="mb-3">Poser une question</h1>
  <form method="post" action="/questions" class="card card-body p-4" novalidate>
    <?= Csrf::field() ?>
    <div class="mb-3">
      <label for="titre" class="form-label">Titre</label>
      <input type="text" class="form-control<?= invalid($errors, 'titre') ?>" id="titre" name="titre"
             value="<?= e($old['titre']) ?>" required minlength="10" maxlength="255"
             placeholder="Ex. : Comment sécuriser une session PHP ?">
      <?= field_error($errors, 'titre') ?>
      <div class="form-text">10 caractères minimum.</div>
    </div>
    <div class="mb-3">
      <label for="contenu" class="form-label">Détails</label>
      <textarea class="form-control<?= invalid($errors, 'contenu') ?>" id="contenu" name="contenu" rows="8"
                required maxlength="10000"><?= e($old['contenu']) ?></textarea>
      <?= field_error($errors, 'contenu') ?>
    </div>
    <?php
    $selected = $old['tags'];
    $field = 'tags';
    $legend = 'Tags';
    $help = 'Choisissez de 1 à 5 tags.';
    require __DIR__ . '/../partials/skill_picker.php';
    ?>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-accent">Publier la question</button>
      <a class="btn btn-outline-secondary" href="/questions">Annuler</a>
    </div>
  </form>
</div>
