<!-- auth/confirmar_sessao.php -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Sessão Ativa Detectada</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle fa-2x text-warning me-3"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Você já está logado em outro dispositivo!</h5>
                                <p>Detectamos que sua conta está sendo usada em outro navegador ou dispositivo.</p>
                                <p class="mb-0">Por motivos de segurança, permitimos apenas uma sessão ativa por vez.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Detalhes da sessão ativa:</h6>
                            <p class="mb-1"><strong>Usuário:</strong> <?= $usuario['nome'] ?></p>
                            <p class="mb-1"><strong>E-mail:</strong> <?= $usuario['email'] ?></p>
                            <p class="mb-1"><strong>Início da sessão:</strong> <?= date('d/m/Y H:i:s', strtotime($sessao_ativa['session_start'])) ?></p>
                            <p class="mb-0"><strong>Endereço IP:</strong> <?= $sessao_ativa['session_ip'] ?></p>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= base_url('auth/forcar_login') ?>" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Encerrar sessão anterior e entrar
                        </a>
                        <a href="<?= base_url('auth') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Voltar para o login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>