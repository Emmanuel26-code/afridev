<?php
/** Sélection de compétences en pastilles. Attend $catalogue, $selected (int[]), $field, $legend, $help, $errors. */
$grouped = [];
foreach ($catalogue as $skill) {
    $grouped[$skill['categorie'] ?? 'Autres'][] = $skill;
}
?>
<fieldset class="mb-3">
  <legend class="form-label fs-6 mb-1"><?= e($legend) ?></legend>
  <div class="form-text mt-0 mb-2"><?= e($help) ?></div>
  <?php if (isset($errors[$field])): ?><div class="text-danger small mb-2" role="alert"><?= e($errors[$field]) ?></div><?php endif; ?>
  <?php foreach ($grouped as $categorie => $skills): ?>
    <div class="mb-2">
      <div class="small text-body-secondary mb-1"><?= e($categorie) ?></div>
      <div class="d-flex flex-wrap gap-1">
        <?php foreach ($skills as $skill): $sid = (int) $skill['id']; ?>
          <input type="checkbox" class="btn-check" id="<?= e($field) ?>-<?= $sid ?>" name="<?= e($field) ?>[]" value="<?= $sid ?>"
                 autocomplete="off" <?= in_array($sid, $selected, true) ? 'checked' : '' ?>>
          <label class="btn btn-pill" for="<?= e($field) ?>-<?= $sid ?>"><?= e($skill['nom']) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</fieldset>
