<?php
use App\Core\Csrf;
use App\Services\ProfileService;
?>
<h1 class="h4 mb-1">Mes compétences</h1>
<p class="text-body-secondary">Cochez vos compétences et indiquez votre niveau. CODEV s'en sert pour vous recommander.</p>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>

<form method="post" action="/users/me/skills">
  <?= Csrf::field() ?>
  <?php foreach ($catalogue as $categorie => $skills): ?>
    <fieldset class="card mb-3">
      <legend class="card-header h6 mb-0"><?= e($categorie) ?></legend>
      <div class="list-group list-group-flush">
        <?php foreach ($skills as $skill):
            $id = (int) $skill['id'];
            $current = $mine[$id] ?? null; ?>
          <div class="list-group-item d-flex flex-wrap align-items-center gap-2">
            <div class="form-check flex-grow-1 mb-0">
              <input class="form-check-input" type="checkbox" id="skill-<?= $id ?>"
                     name="skills[<?= $id ?>][on]" value="1" <?= $current ? 'checked' : '' ?>>
              <label class="form-check-label" for="skill-<?= $id ?>"><?= e($skill['nom']) ?></label>
            </div>
            <label class="visually-hidden" for="niveau-<?= $id ?>">Niveau en <?= e($skill['nom']) ?></label>
            <select class="form-select form-select-sm w-auto" id="niveau-<?= $id ?>" name="skills[<?= $id ?>][niveau]">
              <?php foreach (ProfileService::NIVEAUX as $value => $label): ?>
                <option value="<?= $value ?>" <?= (int) ($current['niveau'] ?? 1) === $value ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
            <label class="visually-hidden" for="annees-<?= $id ?>">Années d'expérience en <?= e($skill['nom']) ?></label>
            <input type="number" class="form-control form-control-sm input-years" id="annees-<?= $id ?>"
                   name="skills[<?= $id ?>][annees]" min="0" max="50" placeholder="Années"
                   value="<?= e($current['annees_experience'] ?? '') ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </fieldset>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-afridev">Enregistrer mes compétences</button>
</form>
