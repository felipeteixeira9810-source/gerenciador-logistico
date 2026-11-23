</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($scriptsExtras)): ?>
  <?php foreach ($scriptsExtras as $src): ?>
    <script src="<?= e($src) ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
