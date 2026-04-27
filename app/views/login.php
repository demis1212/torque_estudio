<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --surface: #0a0c10;
            --surface-container: #11131a;
            --surface-container-high: #1a1d26;
            --on-surface: #e8eaf2;
            --on-surface-variant: #9aa3b2;
            --primary: #8ab4f8;
            --primary-container: #4d8eff;
            --on-primary: #fff;
            --error: #f87171;
            --success: #4ade80;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .bg-animation .circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 20s infinite ease-in-out;
        }
        
        .bg-animation .circle:nth-child(1) {
            width: 600px;
            height: 600px;
            background: var(--primary-container);
            top: -200px;
            right: -100px;
            animation-delay: 0s;
        }
        
        .bg-animation .circle:nth-child(2) {
            width: 500px;
            height: 500px;
            background: #8b5cf6;
            bottom: -150px;
            left: -100px;
            animation-delay: -5s;
        }
        
        .bg-animation .circle:nth-child(3) {
            width: 400px;
            height: 400px;
            background: var(--primary);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        
        /* Left Side - Branding */
        .brand-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
            z-index: 1;
        }
        
        .brand-content {
            max-width: 480px;
            text-align: center;
        }
        
        .brand-logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 40px;
            font-size: 60px;
            box-shadow: 0 20px 60px rgba(77, 142, 255, 0.3);
            animation: pulse 3s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 20px 60px rgba(77, 142, 255, 0.3); }
            50% { box-shadow: 0 20px 80px rgba(77, 142, 255, 0.5); }
        }
        
        .brand-content h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--on-surface) 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .brand-content p {
            font-size: 18px;
            color: var(--on-surface-variant);
            line-height: 1.6;
            margin-bottom: 48px;
        }
        
        .features {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            background: rgba(255,255,255,0.05);
            transform: translateX(8px);
        }
        
        .feature-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, rgba(138,180,248,0.15) 0%, rgba(77,142,255,0.15) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 18px;
        }
        
        .feature-text {
            text-align: left;
        }
        
        .feature-text strong {
            display: block;
            color: var(--on-surface);
            font-size: 15px;
            margin-bottom: 4px;
        }
        
        .feature-text span {
            color: var(--on-surface-variant);
            font-size: 13px;
        }
        
        /* Right Side - Login Form */
        .login-section {
            width: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            background: linear-gradient(145deg, rgba(26,29,38,0.8) 0%, rgba(17,19,26,0.9) 100%);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.4);
        }
        
        .login-header {
            margin-bottom: 32px;
        }
        
        .login-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--on-surface);
        }
        
        .login-header p {
            color: var(--on-surface-variant);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--on-surface-variant);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .form-label i {
            font-size: 12px;
            color: var(--primary);
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--on-surface-variant);
            transition: color 0.3s;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(10,12,16,0.6);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            color: var(--on-surface);
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary-container);
            background: rgba(10,12,16,0.8);
            box-shadow: 0 0 0 4px rgba(77,142,255,0.1);
        }
        
        .input-wrapper input:focus + i,
        .input-wrapper input:not(:placeholder-shown) + i {
            color: var(--primary);
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--on-surface-variant);
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: var(--on-surface);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .checkbox-wrapper input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-container);
            cursor: pointer;
        }
        
        .checkbox-wrapper span {
            font-size: 13px;
            color: var(--on-surface-variant);
        }
        
        .forgot-link {
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        
        .forgot-link:hover {
            opacity: 0.8;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-container) 0%, #3b7de8 100%);
            color: var(--on-primary);
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(77,142,255,0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login.loading {
            pointer-events: none;
        }
        
        .btn-login.loading .btn-text {
            opacity: 0;
        }
        
        .btn-login .spinner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            opacity: 0;
        }
        
        .btn-login.loading .spinner {
            opacity: 1;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Error Message */
        .error-message {
            background: rgba(248,113,113,0.1);
            border: 1px solid rgba(248,113,113,0.2);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }
        
        .error-message i {
            color: var(--error);
            font-size: 18px;
        }
        
        .error-message span {
            color: var(--error);
            font-size: 14px;
        }
        
        /* Version Footer */
        .version-info {
            text-align: center;
            margin-top: 32px;
            color: var(--on-surface-variant);
            font-size: 12px;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .brand-section {
                display: none;
            }
            .login-section {
                width: 100%;
                padding: 40px 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    
    <!-- Brand Section -->
    <div class="brand-section">
        <div class="brand-content">
            <div class="brand-logo">🔧</div>
            <h1>Torque Studio ERP</h1>
            <p>Sistema integral de gestión para talleres automotrices. Controla órdenes, inventario, herramientas y más desde una sola plataforma.</p>
            
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="feature-text">
                        <strong>Gestión de Órdenes</strong>
                        <span>Seguimiento completo del flujo de trabajo</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-boxes"></i></div>
                    <div class="feature-text">
                        <strong>Control de Inventario</strong>
                        <span>Alertas de stock y gestión de refacciones</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-tools"></i></div>
                    <div class="feature-text">
                        <strong>Herramientas Inteligentes</strong>
                        <span>Préstamos y seguimiento de equipos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Login Section -->
    <div class="login-section">
        <div class="login-card">
            <div class="login-header">
                <h2>Bienvenido de vuelta</h2>
                <p>Ingresa tus credenciales para acceder al sistema</p>
            </div>
            
            <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= esc($error) ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" required autocomplete="email">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <i class="fas fa-lock"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember">
                        <span>Recordarme</span>
                    </label>
                    <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="btn-login" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Ingresar al Sistema</span>
                    <div class="spinner"></div>
                </button>
            </form>
        </div>
        
        <div class="version-info">
            Torque Studio ERP v2.0 • Sistema de Gestión Automotriz
        </div>
    </div>
    
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Loading State
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
        });
        
        // Input Animations
        document.querySelectorAll('.input-wrapper input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
