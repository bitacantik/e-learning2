<?php
include 'session.php';
include 'connect.php';

if ($_SESSION['role'] !== 'mahasiswa') {
    $_SESSION['error'] = "Akses ditolak. Hanya mahasiswa yang dapat mengakses halaman ini.";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - E-Learning</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d63384;
            --primary-dark: #a61e4d;
            --secondary: #ae3ec9;
            --accent: #f06595;
            --text: #495057;
            --light: #fff0f6;
            --white: #ffffff;
            --success: #40c057;
            --warning: #f59f00;
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
            background-color: #f8f9fa;
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px;
            margin-bottom: 40px;
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 222, 235, 0.5);
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }

        .welcome-message {
            font-size: 2rem;
            color: var(--text);
            position: relative;
        }

        .welcome-message span {
            font-weight: 700;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
            position: relative;
        }

        .welcome-message span::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
        }

        .nav-link {
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .nav-link::before {
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

        .nav-link:hover::before {
            opacity: 1;
        }

        .nav-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(166, 30, 77, 0.25);
        }

        .nav-link.warning {
            background: linear-gradient(135deg, var(--warning), #f08c00);
        }

        .nav-link.warning:hover {
            background: linear-gradient(135deg, #e68a00, #d68000);
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .card {
            background-color: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 222, 235, 0.5);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(214, 51, 132, 0.2);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }

        .card-header {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(214, 51, 132, 0.1);
        }

        .card-header i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .card-body {
            color: var(--text);
        }

        .task-item {
            padding: 16px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .task-item:hover {
            background-color: rgba(255, 222, 235, 0.1);
            padding-left: 10px;
            border-radius: 8px;
        }

        .task-name {
            font-weight: 500;
            position: relative;
            padding-left: 20px;
        }

        .task-name::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background-color: var(--primary);
            border-radius: 50%;
        }

        .task-deadline {
            color: var(--warning);
            font-size: 0.95rem;
            font-weight: 500;
            background-color: rgba(245, 159, 0, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .task-completed {
            color: var(--success);
            font-size: 0.95rem;
            font-weight: 500;
            background-color: rgba(64, 192, 87, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .task-time {
            background-color: rgba(214, 51, 132, 0.1);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        @media (max-width: 992px) {
            .dashboard-header {
                flex-direction: column;
                gap: 25px;
                text-align: center;
            }

            .welcome-message {
                font-size: 1.8rem;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .dashboard-container {
                padding: 20px;
            }

            .dashboard-header {
                padding: 25px 20px;
            }

            .card {
                padding: 25px 20px;
            }

            .card-header {
                font-size: 1.3rem;
            }

            .nav-link {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="welcome-message">Selamat datang, <span><?php echo $_SESSION['name']; ?></span></h1>
            <div class="nav-menu">
                <a href="submit_tugas.php" class="nav-link warning">
                    <i class="fas fa-file-upload"></i>
                    Submit Tugas
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tasks"></i> Tugas Mendatang
                </div>
                <div class="card-body">
                    <div class="task-item">
                        <span class="task-name">Tugas Pemrograman Web</span>
                        <span class="task-deadline">2 hari lagi</span>
                    </div>
                    <div class="task-item">
                        <span class="task-name">Laporan Basis Data</span>
                        <span class="task-deadline">5 hari lagi</span>
                    </div>
                    <div class="task-item">
                        <span class="task-name">Presentasi AI</span>
                        <span class="task-deadline">1 minggu lagi</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-check-circle"></i> Tugas Selesai
                </div>
                <div class="card-body">
                    <div class="task-item">
                        <span class="task-name">Kuis Matematika</span>
                        <span class="task-completed">Terkumpul</span>
                    </div>
                    <div class="task-item">
                        <span class="task-name">Makalah Etika</span>
                        <span class="task-completed">Terkumpul</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt"></i> Jadwal Hari Ini
                </div>
                <div class="card-body">
                    <div class="task-item">
                        <span class="task-name">Pemrograman Web</span>
                        <span class="task-time">09:00 - 11:00</span>
                    </div>
                    <div class="task-item">
                        <span class="task-name">Basis Data</span>
                        <span class="task-time">13:00 - 15:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>