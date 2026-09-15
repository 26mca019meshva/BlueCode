
</div><!-- /.content-area -->
</main><!-- /.main-content -->
</div><!-- /.app-wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- App JS -->
<script src="<?= URL_ROOT ?>assets/js/app.js"></script>

<?php if (isset($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= URL_ROOT ?>assets/js/<?= $script ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inlineScript)): ?>
<script><?= $inlineScript ?></script>
<?php endif; ?>

</body>
</html>
