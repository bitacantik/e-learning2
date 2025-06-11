<?php
session_start();
include 'connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'dosen' ? 'dashboard_dosen.php' : 'dashboard_mahasiswa.php'));
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['last_login'] = time();

                    header("Location: " . ($user['role'] == 'dosen' ? 'dashboard_dosen.php' : 'dashboard_mahasiswa.php'));
                    exit();
                }
            }
            
            $error = "Email atau password salah";
            error_log("Failed login attempt for email: $email");
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
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
    <title>Login - E-Learning</title>
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

        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px;
            position: relative;
            z-index: 2;
            overflow: hidden;
            border: 1px solid rgba(255, 222, 235, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(214, 51, 132, 0.3);
            transform: rotate(15deg);
            transition: var(--transition);
        }

        .logo-icon:hover {
            transform: rotate(0deg) scale(1.1);
        }

        .logo h1 {
            color: var(--primary);
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
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
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

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text);
            opacity: 0.6;
            transition: var(--transition);
        }

        .password-toggle:hover {
            opacity: 1;
            color: var(--primary);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
            margin-top: 10px;
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
            box-shadow: 0 8px 20px rgba(166, 30, 77, 0.25);
        }

        .footer-links {
            margin-top: 30px;
            text-align: center;
            font-size: 0.95rem;
        }

        .footer-links p {
            margin-bottom: 12px;
            color: var(--text);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .footer-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .footer-links a:hover::after {
            width: 100%;
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            animation: float 15s infinite linear;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            background: var(--primary);
            top: 15%;
            left: 10%;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            background: var(--secondary);
            bottom: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
            100% { transform: translateY(0) rotate(360deg); }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
            
            .form-control {
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
    
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-laptop-code"></i>
            </div>
            <h1>E-Learning</h1>
            <p>Sistem Pembelajaran Online</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="Masukkan email Anda" required
                       value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Masukkan password Anda" required>
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="footer-links">
            <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
            <p><a href="forgot_password.php">Lupa password?</a></p>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>