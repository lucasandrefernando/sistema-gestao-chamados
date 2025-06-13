<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="my-2"><?= APP_NAME ?></h4>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="<?= base_url('public/img/logo.png') ?>" alt="Logo" height="80" class="mb-3">
                    <h5>Hospital Madre Teresa</h5>
                </div>

                <form action="<?= base_url('auth/login') ?>" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= base_url('auth/recuperarSenha') ?>" class="text-decoration-none">Esqueceu sua senha?</a>
                </div>
            </div>
            <div class="card-footer text-center text-muted">
                <small>&copy; <?= date('Y') ?> Eagle Telecom</small>
            </div>
        </div>
    </div>
</div>