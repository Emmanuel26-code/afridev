<section class="hero bg-body-tertiary rounded p-4 p-md-5 mb-4">
  <h1 class="fw-bold text-afridev">La plateforme d'échange des développeurs africains</h1>
  <p class="lead mb-4">Entraidez-vous, présentez vos projets et trouvez les bons collaborateurs grâce à CODEV, le matching assisté par l'IA.</p>
  <?php if (!\App\Core\Auth::user()): ?>
    <a class="btn btn-afridev btn-lg me-2" href="/auth/register">Créer mon compte</a>
    <a class="btn btn-outline-secondary btn-lg" href="/auth/login">Se connecter</a>
  <?php endif; ?>
</section>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h2 class="h5">Entraide</h2>
      <p class="card-text">Posez vos questions techniques et répondez à celles de la communauté.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h2 class="h5">Projets</h2>
      <p class="card-text">Publiez vos projets avec leurs technologies, leur dépôt Git et leur démo.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h2 class="h5">CODEV</h2>
      <p class="card-text">Décrivez votre besoin : l'IA recommande les développeurs les plus adaptés, avec une explication.</p>
    </div></div>
  </div>
</div>
