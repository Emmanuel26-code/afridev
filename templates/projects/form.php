<?php
use App\Core\Csrf;
use App\Models\Project;

$action = $id === null ? '/projects' : '/projects/' . $id . '/edit';
?>
<div class="mx-auto form-wide">
  <h1 class="mb-3"><?= $id === null ? 'Publier un projet' : 'Modifier le projet' ?></h1>
  <form method="post" action="<?= e($action) ?>" class="card card-body p-4" novalidate>
    <?= Csrf::field() ?>
    <div class="row g-3 mb-3">
      <div class="col-md-8">
        <label for="titre" class="form-label">Titre</label>
        <input type="text" class="form-control<?= invalid($errors, 'titre') ?>" id="titre" name="titre"
               value="<?= e($old['titre']) ?>" required minlength="3" maxlength="150">
        <?= field_error($errors, 'titre') ?>
      </div>
      <div class="col-md-4">
        <label for="statut" class="form-label">Statut</label>
        <select class="form-select<?= invalid($errors, 'statut') ?>" id="statut" name="statut">
          <?php foreach (Project::STATUTS as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $value === $old['statut'] ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'statut') ?>
      </div>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control<?= invalid($errors, 'description') ?>" id="description" name="description" rows="6"
                required minlength="10" maxlength="5000"><?= e($old['description']) ?></textarea>
      <?= field_error($errors, 'description') ?>
    </div>
    <?php
    $selected = $old['skills'];
    $field = 'skills';
    $legend = 'Technologies';
    $help = 'Choisissez de 1 à 10 technologies.';
    require __DIR__ . '/../partials/skill_picker.php';
    ?>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label for="lien_git" class="form-label">Lien Git <span class="text-body-secondary">(facultatif)</span></label>
        <input type="url" class="form-control<?= invalid($errors, 'lien_git') ?>" id="lien_git" name="lien_git"
               value="<?= e($old['lien_git']) ?>" maxlength="255" placeholder="https://github.com/…">
        <?= field_error($errors, 'lien_git') ?>
      </div>
      <div class="col-md-6">
        <label for="lien_demo" class="form-label">Lien de démo <span class="text-body-secondary">(facultatif)</span></label>
        <input type="url" class="form-control<?= invalid($errors, 'lien_demo') ?>" id="lien_demo" name="lien_demo"
               value="<?= e($old['lien_demo']) ?>" maxlength="255" placeholder="https://…">
        <?= field_error($errors, 'lien_demo') ?>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-accent"><?= $id === null ? 'Publier le projet' : 'Enregistrer' ?></button>
      <a class="btn btn-outline-secondary" href="<?= $id === null ? '/projects' : '/projects/' . (int) $id ?>">Annuler</a>
    </div>
  </form>
</div>
