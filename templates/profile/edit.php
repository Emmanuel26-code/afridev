<?php
use App\Core\Csrf;
use App\Services\ProfileService;
?>
<div class="mx-auto form-narrow">
  <h1 class="h4 mb-3">Modifier mon profil</h1>
  <form method="post" action="/users/me" novalidate>
    <?= Csrf::field() ?>
    <div class="row g-3">
      <div class="col-sm-6">
        <label for="nom" class="form-label">Nom complet</label>
        <input type="text" class="form-control<?= invalid($errors, 'nom') ?>" id="nom" name="nom"
               value="<?= e($old['nom'] ?? '') ?>" required minlength="2" maxlength="100">
        <?= field_error($errors, 'nom') ?>
      </div>
      <div class="col-sm-6">
        <label for="pseudo" class="form-label">Pseudo</label>
        <input type="text" class="form-control<?= invalid($errors, 'pseudo') ?>" id="pseudo" name="pseudo"
               value="<?= e($old['pseudo'] ?? '') ?>" required maxlength="50" pattern="[A-Za-z0-9_.\-]{3,50}">
        <?= field_error($errors, 'pseudo') ?>
      </div>
      <div class="col-12">
        <label for="bio" class="form-label">Bio</label>
        <textarea class="form-control<?= invalid($errors, 'bio') ?>" id="bio" name="bio" rows="4"
                  maxlength="500"><?= e($old['bio'] ?? '') ?></textarea>
        <?= field_error($errors, 'bio') ?>
        <div class="form-text">500 caractères maximum.</div>
      </div>
      <div class="col-sm-6">
        <label for="pays_ville" class="form-label">Pays / ville</label>
        <input type="text" class="form-control<?= invalid($errors, 'pays_ville') ?>" id="pays_ville" name="pays_ville"
               value="<?= e($old['pays_ville'] ?? '') ?>" maxlength="100" placeholder="Côte d'Ivoire, Abidjan">
        <?= field_error($errors, 'pays_ville') ?>
      </div>
      <div class="col-sm-6">
        <label for="disponibilite" class="form-label">Disponibilité</label>
        <select class="form-select<?= invalid($errors, 'disponibilite') ?>" id="disponibilite" name="disponibilite">
          <?php foreach (ProfileService::DISPONIBILITES as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= ($old['disponibilite'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'disponibilite') ?>
      </div>
      <div class="col-12">
        <label for="github_url" class="form-label">Profil GitHub</label>
        <input type="url" class="form-control<?= invalid($errors, 'github_url') ?>" id="github_url" name="github_url"
               value="<?= e($old['github_url'] ?? '') ?>" maxlength="255" placeholder="https://github.com/pseudo">
        <?= field_error($errors, 'github_url') ?>
      </div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-afridev">Enregistrer</button>
      <a class="btn btn-outline-secondary" href="/users/<?= (int) \App\Core\Auth::id() ?>">Annuler</a>
    </div>
  </form>
</div>
