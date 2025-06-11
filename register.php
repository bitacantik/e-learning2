<?php
session_start();
include 'connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'dosen' ? 'dashboard_dosen.php' : 'dashboard_mahasiswa.php'));
    exit();
}

$error = '';
$formData = [
    'name' => '',
    'email' => '',
    'role' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formData['name'] = trim($_POST['name']);
    $formData['email'] = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $formData['role'] = $_POST['role'];

    if (empty($formData['name']) || empty($formData['email']) || empty($password) || empty($formData['role'])) {
        $error = "Semua field harus diisi";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } elseif (strlen($password) < 8) {
        $error = "Password harus minimal 8 karakter";
    } elseif ($password !== $confirmPassword) {
        $error = "Password tidak cocok";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $formData['email']);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "Email sudah digunakan";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $formData['name'], $formData['email'], $passwordHash, $formData['role']);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Pendaftaran berhasil! Silakan login.";
                    header("Location: login.php");
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Registration Error: " . $e->getMessage());
            $error = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - E-Learning</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d63384;
            --primary-dark: #a61e4d;
            --secondary: #ae3ec9;
            --text: #495057;
            --light: #fff0f6;
            --white: #ffffff;
            --danger: #fa5252;
            --success: #40c057;
            --shadow: 0 4px 20px rgba(214, 51, 132, 0.15);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #fff0f6 0%, #f8f0fc 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .register-container {
            width: 100%;
            max-width: 500px;
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px;
            position: relative;
            z-index: 2;
            border: 1px solid rgba(255, 222, 235, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(214, 51, 132, 0.3);
            transform: rotate(15deg);
            transition: var(--transition);
        }

        .logo-icon:hover {
            transform: rotate(0deg) scale(1.1);
        }

        .logo h1 {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .logo p {
            color: var(--text);
            opacity: 0.8;
            font-size: 1rem;
            font-weight: 400;
        }

        .alert {
            padding: 14px 16px;
            background-color: #fff5f5;
            color: var(--danger);
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid var(--danger);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .alert i {
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--text);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(214, 51, 132, 0.15);
            background-color: var(--white);
        }

        .select-role {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            background-color: #f8f9fa;
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23d63384' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
        }

        .select-role:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(214, 51, 132, 0.15);
            background-color: var(--white);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-dark), #8c2cb3);
            opacity: 0;
            transition: var(--transition);
            z-index: -1;
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(166, 30, 77, 0.25);
        }

        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .password-strength i {
            color: var(--primary);
        }

        .login-link {
            margin-top: 30px;
            text-align: center;
            font-size: 0.95rem;
            color: var(--text);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .login-link a:hover::after {
            width: 100%;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 25px;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
            
            .form-control, .select-role {
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-laptop-code"></i>
            </div>
            <h1>E-Learning</h1>
            <p>Buat akun baru</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="form-control" 
                       placeholder="Masukkan nama lengkap" required
                       value="<?php echo htmlspecialchars($formData['name']); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="Masukkan email" required
                       value="<?php echo htmlspecialchars($formData['email']); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Buat password (minimal 8 karakter)" required>
                <div class="password-strength">
                    <i class="fas fa-info-circle"></i> Gunakan kombinasi huruf, angka, dan simbol
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Ulangi password Anda" required>
            </div>

            <div class="form-group">
                <label for="role">Peran</label>
                <select id="role" name="role" class="select-role" required>
                    <option value="">Pilih peran Anda</option>
                    <option value="dosen" <?= $formData['role'] === 'dosen' ? 'selected' : '' ?>>Dosen</option>
                    <option value="mahasiswa" <?= $formData['role'] === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                </select>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>

        <div class="login-link">
            <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
        </div>
    </div>

    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Password tidak cocok");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;
    </script>
</body>
</html>