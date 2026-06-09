<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>
<div class="container d-flex align-items-center justify-content-center" style="min-height: 55vh;">
    <div class="login-container w-100">
        <div class="login-card">
            <div class="login-header">
                <img src="<?= base_url('images/logoTurix.png') ?>" alt="TurixShop Logo" class="login-logo">
                <h4 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif; letter-spacing: 1px;">
                    TURIX<span style="color: var(--turix-yellow);">SHOP</span>
                </h4>
                <p class="text-white-50 small mb-0">Panel de Administración</p>
            </div>

            <div class="login-body">
                <!-- Alertas de Sesión -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-premium d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <div><?= session()->getFlashdata('error') ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert"
                        style="background: rgba(40, 167, 69, 0.15); border: 1px solid rgba(40, 167, 69, 0.3); color: #d4edda; border-radius: 12px;">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div><?= session()->getFlashdata('success') ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="POST" hx-boost="true">
                    <?= csrf_field() ?>

                    <!-- Campo de Usuario -->
                    <div class="mb-4">
                        <label for="username" class="form-label form-label-premium">Usuario o Correo</label>
                        <div class="input-group input-group-premium">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Tu usuario o email" required autocomplete="username">
                        </div>
                    </div>

                    <!-- Campo de Contraseña -->
                    <div class="mb-4">
                        <label for="password" class="form-label form-label-premium">Contraseña</label>
                        <div class="input-group input-group-premium">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                            <span class="input-group-text" id="togglePassword" style="cursor: pointer; padding-right: 1rem;" title="Mostrar/ocultar contraseña">
                                <i class="bi bi-eye-fill" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Botón Ingresar -->
                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-login-premium">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (togglePassword) {
            togglePassword.addEventListener('click', function () {
                // Alternar tipo
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Alternar icono
                if (type === 'password') {
                    toggleIcon.classList.remove('bi-eye-slash-fill');
                    toggleIcon.classList.add('bi-eye-fill');
                } else {
                    toggleIcon.classList.remove('bi-eye-fill');
                    toggleIcon.classList.add('bi-eye-slash-fill');
                }
            });

            // Efecto hover
            togglePassword.addEventListener('mouseenter', () => {
                togglePassword.style.color = '#ffffff';
            });
            togglePassword.addEventListener('mouseleave', () => {
                togglePassword.style.color = 'rgba(255, 255, 255, 0.5)';
            });
        }
    });
</script>
<?= $this->endSection() ?>