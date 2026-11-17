<section class="hero mb-5">
  <span class="eyebrow mb-3">Pour les devs, par les devs</span>
  <h1 class="mt-3">La plateforme des<br><span class="text-afridev">développeurs africains.</span></h1>
  <p class="lead mt-3 mb-4">Entraidez-vous, présentez vos projets et trouvez les bons collaborateurs grâce à CODEV, le matching assisté par l'IA.</p>
  <div class="d-flex flex-wrap justify-content-center gap-2">
    <?php if (\App\Core\Auth::user()): ?>
      <a class="btn btn-accent btn-lg" href="/users/<?= (int) \App\Core\Auth::id() ?>">Mon profil</a>
      <a class="btn btn-outline-secondary btn-lg" href="/users/me/skills">Mes compétences</a>
    <?php else: ?>
      <a class="btn btn-accent btn-lg" href="/auth/register">Créer un compte</a>
      <a class="btn btn-outline-secondary btn-lg" href="/auth/login">J’ai déjà un compte</a>
    <?php endif; ?>
  </div>
</section>

<h2 class="display-title fs-1 mb-4">Trois outils.<br><span class="text-accent">Une seule communauté.</span></h2>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body p-4">
      <span class="feature-icon blue" aria-hidden="true">?</span>
      <h3 class="h5"><a class="stretched-link text-reset text-decoration-none" href="/questions">Entraide</a></h3>
      <p class="card-text text-body-secondary">Posez vos questions techniques et répondez à celles de la communauté.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body p-4">
      <span class="feature-icon orange" aria-hidden="true">&lt;/&gt;</span>
      <h3 class="h5"><a class="stretched-link text-reset text-decoration-none" href="/projects">Projets</a></h3>
      <p class="card-text text-body-secondary">Publiez vos projets avec leurs technologies, leur dépôt Git et leur démo.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body p-4">
      <span class="feature-icon blue" aria-hidden="true">IA</span>
      <h3 class="h5">CODEV</h3>
      <p class="card-text text-body-secondary">Décrivez votre besoin : l'IA recommande les développeurs les plus adaptés, avec une explication.</p>
    </div></div>
  </div>
</div>
