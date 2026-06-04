<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .login-container {
        max-width: 450px;
        margin: 4rem auto;
    }
    .login-card {
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 18, 51, 0.3), 0 0 25px rgba(255, 204, 0, 0.05);
        color: #ffffff;
        overflow: hidden;
    }
    .login-header {
        background: linear-gradient(135deg, rgba(0, 18, 51, 0.5) 0%, rgba(15, 23, 42, 0.8) 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 2.5rem 2rem 1.5rem 2rem;
        text-align: center;
    }
    .login-logo {
        height: 80px;
        width: 80px;
        object-fit: contain;
        filter: drop-shadow(0 0 10px rgba(255, 204, 0, 0.4));
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
    }
    .login-logo:hover {
        transform: scale(1.08) rotate(3deg);
    }
    .login-body {
        padding: 2.5rem 2rem;
    }
    .form-label-premium {
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 0.5rem;
    }
    .input-group-premium {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .input-group-premium:focus-within {
        border-color: var(--turix-yellow);
        box-shadow: 0 0 12px rgba(255, 204, 0, 0.25);
        background: rgba(255, 255, 255, 0.07);
    }
    .input-group-premium .input-group-text {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.5);
        padding-left: 1rem;
        padding-right: 0.5rem;
    }
    .input-group-premium .form-control {
        background: transparent;
        border: none;
        color: #ffffff;
        padding: 0.75rem 1rem 0.75rem 0.5rem;
        font-size: 0.95rem;
    }
    .input-group-premium .form-control:focus {
        box-shadow: none;
        background: transparent;
        color: #ffffff;
    }
    .input-group-premium .form-control::placeholder {
        color: rgba(255, 255, 255, 0.45) !important;
        opacity: 1; /* For Firefox */
    }
    .btn-login-premium {
        background: linear-gradient(135deg, var(--turix-accent) 0%, #ff6600 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        padding: 0.8rem;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(255, 140, 0, 0.35);
    }
    .btn-login-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(255, 140, 0, 0.5);
        filter: brightness(1.05);
    }
    .btn-login-premium:active {
        transform: translateY(0);
    }
    .alert-premium {
        background: rgba(220, 53, 69, 0.15);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #f8d7da;
        border-radius: 12px;
        font-size: 0.9rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container d-flex align-items-center justify-content-center" style="min-height: 70vh;">
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
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert" style="background: rgba(40, 167, 69, 0.15); border: 1px solid rgba(40, 167, 69, 0.3); color: #d4edda; border-radius: 12px;">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div><?= session()->getFlashdata('success') ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <!-- Campo de Usuario -->
                    <div class="mb-4">
                        <label for="username" class="form-label form-label-premium">Usuario o Correo</label>
                        <div class="input-group input-group-premium">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Tu usuario o email" required autocomplete="username">
                        </div>
                    </div>

                    <!-- Campo de Contraseña -->
                    <div class="mb-4">
                        <label for="password" class="form-label form-label-premium">Contraseña</label>
                        <div class="input-group input-group-premium">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
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
<?= $this->endSection() ?>
