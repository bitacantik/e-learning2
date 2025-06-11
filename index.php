<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'dosen') {
        header("Location: dashboard_dosen.php");
    } else {
        header("Location: dashboard_mahasiswa.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama - Classroom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #d63384;
            --primary-dark: #a61e4d;
            --secondary: #ae3ec9;
            --text: #495057;
            --light: #fff0f6;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(214, 51, 132, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .container {
            width: 100%;
            max-width: 500px;
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(214, 51, 132, 0.15);
            padding: 40px;
            text-align: center;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 222, 235, 0.5);
            animation: fadeIn 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        h1 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.2;
        }

        p {
            color: var(--text);
            margin-bottom: 30px;
            font-size: 1.1rem;
            opacity: 0.8;
            line-height: 1.6;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .button {
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(214, 51, 132, 0.3);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
            min-width: 160px;
        }

        .button::before {
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

        .button:hover::before {
            opacity: 1;
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(166, 30, 77, 0.3);
        }

        .button.secondary {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            box-shadow: none;
        }

        .button.secondary:hover {
            background: var(--primary);
            color: var(--white);
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
            opacity: 0.1;
            animation: float 15s infinite linear;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            background: var(--primary);
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            background: var(--secondary);
            bottom: 15%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 100px;
            height: 100px;
            background: var(--primary-dark);
            top: 60%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
            100% { transform: translateY(0) rotate(360deg); }
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(214, 51, 132, 0.3);
            transform: rotate(15deg);
            transition: var(--transition);
        }

        .logo:hover {
            transform: rotate(0deg) scale(1.1);
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .button-group {
                flex-direction: column;
                gap: 12px;
            }
            
            .button {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <div class="container animate__animated animate__fadeIn">
        <div class="logo">C</div>
        <h1>Selamat Datang di Classroom</h1>
        <p>Sistem pembelajaran online yang menghubungkan dosen dan mahasiswa dalam lingkungan digital yang interaktif</p>
        <div class="button-group">
            <a href="login.php" class="button">Login</a>
            <a href="register.php" class="button secondary">Daftar</a>
        </div>
    </div>
</body>
</html>