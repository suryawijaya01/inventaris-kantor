<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/auth.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$auth = new Auth();
$error = '';
$success = '';
$basePath = $_ENV['BASE_PATH'];

// If already logged in, redirect to admin
if ($auth->isLoggedIn()) {
    header('Location: ' . $basePath . '/admin/index.php');
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $departemen = trim($_POST['departemen']);
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi';
    } elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok';
    } else {
        if ($auth->register($username, $password, 'pegawai', $departemen)) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = 'Username sudah digunakan';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Inventaris</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .register-left {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .register-left h1 {
            font-size: 36px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .register-left p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .register-right {
            padding: 60px 40px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .register-header h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #dfe6e9;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #27ae60;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .login-link a {
            color: #27ae60;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .register-container {
                grid-template-columns: 1fr;
            }
            
            .register-left {
                padding: 40px 30px;
            }
            
            .register-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-left">
            <h1><i class="fas fa-user-plus"></i> DAFTAR</h1>
            <p>Buat akun baru untuk mengakses Sistem Manajemen Inventaris Kantor dan mulai mengelola aset dengan lebih efisien.</p>
            <div style="margin-top: 30px;">
                <p><i class="fas fa-check-circle"></i> Proses pendaftaran cepat</p>
                <p><i class="fas fa-check-circle"></i> Akses ke semua fitur</p>
                <p><i class="fas fa-check-circle"></i> Aman dan terpercaya</p>
            </div>
        </div>
        
        <div class="register-right">
            <div class="register-header">
                <h2>Buat Akun Baru</h2>
                <p>Isi formulir di bawah untuk mendaftar</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Departemen</label>
                    <div class="input-group">
                        <i class="fas fa-building"></i>
                        <input type="text" name="departemen" class="form-control" placeholder="Masukkan departemen (opsional)">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    Daftar
                </button>
            </form>
            
            <div class="login-link">
                Sudah punya akun? <a href="<?php echo $basePath; ?>/login.php">Login di sini</a>
            </div>
        </div>
    </div>
</body>
</html>