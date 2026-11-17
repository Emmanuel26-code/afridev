<?php use App\Core\Csrf; ?>
<div class="card auth-card mx-auto">
  <div class="card-body p-4">
    <h1 class="h4 mb-3">Connexion</h1>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="/auth/login">
      <?= Csrf::field() ?>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= e($old['email'] ?? '') ?>"
               required maxlength="150" autocomplete="email">
      </div>
      <div class="mb-4">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" required maxlength="72"
               autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-afridev w-100">Se connecter</button>
    </form>
    <p class="mt-3 mb-0 text-center small">Pas encore de compte ? <a href="/auth/register">S'inscrire</a></p>
  </div>
</div>
