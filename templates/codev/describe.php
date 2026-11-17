<?php use App\Core\Csrf; ?>
<div class="mx-auto form-wide">
  <span class="eyebrow">CODEV · étape 1 sur 2</span>
  <h1 class="mt-2 mb-1">Décrivez votre besoin</h1>
  <p class="text-body-secondary">Écrivez librement ce que vous cherchez. L'IA en tire le rôle et les compétences ; vous vérifiez avant de publier.</p>
  <form method="post" action="/codev/needs/analyze" class="card card-body p-4" novalidate>
    <?= Csrf::field() ?>
    <div class="mb-3">
      <label for="titre" class="form-label">Titre</label>
      <input type="text" class="form-control<?= invalid($errors, 'titre') ?>" id="titre" name="titre"
             value="<?= e($old['titre']) ?>" required minlength="5" maxlength="150"
             placeholder="Ex. : Application de gestion scolaire">
      <?= field_error($errors, 'titre') ?>
    </div>
    <div class="mb-4">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control<?= invalid($errors, 'description') ?>" id="description" name="description" rows="6"
                required minlength="20" maxlength="2000"
                placeholder="Ex. : Je cherche un développeur Flutter pour une application de gestion scolaire, avec une API en PHP."><?= e($old['description']) ?></textarea>
      <?= field_error($errors, 'description') ?>
      <div class="form-text">20 à 2 000 caractères. N'indiquez aucune donnée personnelle.</div>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-accent">Analyser avec l'IA</button>
      <a class="btn btn-outline-secondary" href="/codev">Annuler</a>
    </div>
  </form>
</div>
