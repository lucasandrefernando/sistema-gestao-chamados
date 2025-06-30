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

    <script src="<?= base_url('public/js/header.js') ?>"></script>
    <script src="<?= base_url('public/js/charts.js') ?>"></script>

    <script src="<?= base_url('public/js/main.js') ?>"></script>



    <script src="<?= base_url('public/js/session-check.js') ?>"></script>


    <script src="<?= base_url('public/js/usuarios.js') ?>"></script>
    <script src="<?= base_url('public/js/usuario-form.js') ?>"></script>

    <script src="<?= base_url('public/js/chamados.js') ?>"></script>
    <script src="<?= base_url('public/js/chamado-visualizar.js') ?>"></script>
    <script src="<?= base_url('public/js/chamados-listar.js') ?>"></script>
    <script src="<?= base_url('public/js/chamados-index.js') ?>"></script>
    <script src="<?= base_url('public/js/chamados-form.js') ?>"></script>
    <script src="<?= base_url('public/js/chamados-imprimir.js') ?>"></script>
    <script src="<?= base_url('public/js/chamados-relatorio.js') ?>"></script>

    <script src="<?= base_url('public/js/setores.js') ?>"></script>
    <script src="<?= base_url('public/js/usuarios-setor.js') ?>"></script>
    <script src="<?= base_url('public/js/setores-visualizacao.js') ?>"></script>
    <script src="<?= base_url('public/js/setores-form.js') ?>"></script>
    <script src="<?= base_url('public/js/setores-detalhes.js') ?>"></script>
    <script src="<?= base_url('public/js/setores-admin.js') ?>"></script>
    <script src="<?= base_url('public/js/setores-usuarios.js') ?>"></script>

    <script src="<?= base_url('public/js/licencas.js') ?>"></script>

    <script src="<?= base_url('public/js/dashboard.js') ?>"></script>




    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?= base_url('public/js/' . $script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>


    </body>

    </html>