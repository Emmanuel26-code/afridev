<?php
use App\Core\Csrf;
use App\Models\Need;
use App\Services\ProfileService;
?>
<div class="mx-auto form-wide">
  <span class="eyebrow">CODEV · étape 2 sur 2</span>
  <h1 class="mt-2 mb-1">Vérifiez avant de publier</h1>
  <p class="text-body-secondary">Corrigez librement : rien n'est publié sans votre validation.</p>

  <?php if ($analysis !== null): ?>
    <?php if ($analysis['source'] === 'ia'): ?>
      <div class="alert alert-ai" role="status">
        <strong>Suggestion générée par IA.</strong> Rôle et compétences proposés à partir de votre description.
        <?php if ($analysis['mots_cles']): ?>
          <div class="small mt-1">Mots-clés repérés : <?= e(implode(', ', $analysis['mots_cles'])) ?></div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning" role="status"><?= e($analysis['message']) ?></div>
    <?php endif; ?>
  <?php endif; ?>

  <form method="post" action="/codev/needs" class="card card-body p-4" novalidate>
    <?= Csrf::field() ?>
    <div class="mb-3">
      <label for="titre" class="form-label">Titre</label>
      <input type="text" class="form-control<?= invalid($errors, 'titre') ?>" id="titre" name="titre"
             value="<?= e($old['titre']) ?>" required minlength="5" maxlength="150">
      <?= field_error($errors, 'titre') ?>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control<?= invalid($errors, 'description') ?>" id="description" name="description" rows="4"
                required minlength="20" maxlength="2000"><?= e($old['description']) ?></textarea>
      <?= field_error($errors, 'description') ?>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="role" class="form-label">Rôle recherché</label>
        <input type="text" class="form-control<?= invalid($errors, 'role') ?>" id="role" name="role"
               value="<?= e($old['role']) ?>" required minlength="2" maxlength="100" placeholder="Ex. : Développeur mobile">
        <?= field_error($errors, 'role') ?>
      </div>
      <div class="col-md-3">
        <label for="niveau" class="form-label">Niveau minimum</label>
        <select class="form-select<?= invalid($errors, 'niveau') ?>" id="niveau" name="niveau">
          <?php foreach (ProfileService::NIVEAUX as $value => $label): ?>
            <option value="<?= $value ?>" <?= $value === (int) $old['niveau'] ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'niveau') ?>
      </div>
      <div class="col-md-3">
        <label for="disponibilite" class="form-label">Disponibilité</label>
        <select class="form-select<?= invalid($errors, 'disponibilite') ?>" id="disponibilite" name="disponibilite">
          <?php foreach (Need::DISPONIBILITES as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $value === $old['disponibilite'] ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'disponibilite') ?>
      </div>
    </div>

    <?php
    $selected = $old['requises'];
    $field = 'requises';
    $legend = 'Compétences requises';
    $help = 'De 1 à 10 compétences indispensables.';
    require __DIR__ . '/../partials/skill_picker.php';

    $selected = $old['souhaitees'];
    $field = 'souhaitees';
    $legend = 'Compétences souhaitées';
    $help = 'Facultatif : un plus, mais pas indispensable.';
    require __DIR__ . '/../partials/skill_picker.php';
    ?>

    <div class="mb-4">
      <label for="project_id" class="form-label">Projet lié <span class="text-body-secondary">(facultatif)</span></label>
      <select class="form-select<?= invalid($errors, 'project_id') ?>" id="project_id" name="project_id">
        <option value="">Aucun</option>
        <?php foreach ($projects as $p): ?>
          <option value="<?= (int) $p['id'] ?>" <?= (int) $p['id'] === (int) $old['project_id'] ? 'selected' : '' ?>><?= e($p['titre']) ?></option>
        <?php endforeach; ?>
      </select>
      <?= field_error($errors, 'project_id') ?>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-accent">Publier le besoin</button>
      <a class="btn btn-outline-secondary" href="/codev">Annuler</a>
    </div>
  </form>
</div>
