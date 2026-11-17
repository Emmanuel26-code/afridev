<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;

$me = Auth::user();
$success = Session::pullFlash('success');
$error = Session::pullFlash('error');
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'AFRIDEV') ?> · AFRIDEV</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="/css/app.css">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-md navbar-dark bg-afridev">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">AFRIDEV</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
            aria-controls="nav" aria-expanded="false" aria-label="Ouvrir le menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-md-center gap-md-2">
        <?php if ($me): ?>
          <li class="nav-item"><a class="nav-link" href="/users/<?= (int) $me['id'] ?>">Mon profil</a></li>
          <li class="nav-item"><a class="nav-link" href="/users/me/skills">Mes compétences</a></li>
          <li class="nav-item">
            <form method="post" action="/auth/logout" class="d-inline">
              <?= Csrf::field() ?>
              <button type="submit" class="btn btn-outline-light btn-sm">Déconnexion</button>
            </form>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/auth/login">Connexion</a></li>
          <li class="nav-item"><a class="btn btn-light btn-sm" href="/auth/register">Inscription</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4 flex-grow-1">
  <?php if ($success): ?><div class="alert alert-success" role="status"><?= e($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
  <?= $content ?>
</main>

<footer class="py-3 border-top text-center text-body-secondary small">
  AFRIDEV · pour les devs, par les devs · équipe SHINOBI.3
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
