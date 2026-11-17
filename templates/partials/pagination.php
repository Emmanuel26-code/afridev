<?php /** Attend $path, $params (filtres actifs), $page, $pages. */ ?>
<?php if ($pages > 1): ?>
  <nav aria-label="Pagination" class="mt-4">
    <ul class="pagination justify-content-center">
      <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
        <a class="page-link" href="<?= e(url_with($path, $params + ['page' => $page - 1])) ?>">Précédent</a>
      </li>
      <li class="page-item disabled"><span class="page-link">Page <?= (int) $page ?> sur <?= (int) $pages ?></span></li>
      <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>">
        <a class="page-link" href="<?= e(url_with($path, $params + ['page' => $page + 1])) ?>">Suivant</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>
