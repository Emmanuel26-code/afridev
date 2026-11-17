<?php use App\Core\Csrf; ?>
<div class="card auth-card mx-auto">
  <div class="card-body p-4">
    <h1 class="h4 mb-3">Créer mon compte</h1>
    <form method="post" action="/auth/register" novalidate>
      <?= Csrf::field() ?>
      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label for="nom" class="form-label">Nom</label>
          <input type="text" class="form-control<?= invalid($errors, 'nom') ?>" id="nom" name="nom"
                 value="<?= e($old['nom'] ?? '') ?>" required minlength="2" maxlength="100" autocomplete="family-name">
          <?= field_error($errors, 'nom') ?>
        </div>
        <div class="col-sm-6">
          <label for="prenom" class="form-label">Prénom</label>
          <input type="text" class="form-control<?= invalid($errors, 'prenom') ?>" id="prenom" name="prenom"
                 value="<?= e($old['prenom'] ?? '') ?>" required minlength="2" maxlength="100" autocomplete="given-name">
          <?= field_error($errors, 'prenom') ?>
        </div>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control<?= invalid($errors, 'email') ?>" id="email" name="email"
               value="<?= e($old['email'] ?? '') ?>" required maxlength="150" autocomplete="email">
        <?= field_error($errors, 'email') ?>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control<?= invalid($errors, 'password') ?>" id="password" name="password"
               required minlength="8" maxlength="72" autocomplete="new-password">
        <?= field_error($errors, 'password') ?>
        <div class="form-text">8 caractères minimum.</div>
      </div>
      <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
        <input type="password" class="form-control<?= invalid($errors, 'password_confirmation') ?>"
               id="password_confirmation" name="password_confirmation" required minlength="8" maxlength="72"
               autocomplete="new-password">
        <?= field_error($errors, 'password_confirmation') ?>
      </div>
      <button type="submit" class="btn btn-afridev w-100">Créer mon compte</button>
    </form>
    <p class="mt-3 mb-0 text-center small">Déjà inscrit ? <a href="/auth/login">Se connecter</a></p>
  </div>
</div>
