    <?php if (is_authenticated()): ?>
        </main>
        </div>
        </div>
    <?php else: ?>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- JavaScript do Sistema -->
    <script src="<?= base_url('public/js/utils.js') ?>"></script>
    <script src="<?= base_url('public/js/charts.js') ?>"></script>
    <script src="<?= base_url('public/js/chamados.js') ?>"></script>
    <script src="<?= base_url('public/js/main.js') ?>"></script>
    <script src="<?= base_url('public/js/relatorios.js') ?>"></script>


    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?= base_url('public/js/' . $script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    </body>

    </html>