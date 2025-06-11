<?php
include 'session.php';
include 'connect.php';

if ($_SESSION['role'] !== 'dosen') {
    $_SESSION['error'] = "Akses ditolak. Hanya dosen yang dapat mengakses halaman ini.";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - Classroom</title>
    <style>
        :root {
            --primary: #d63384;
            --primary-dark: #a61e4d;
            --secondary: #ae3ec9;
            --text: #495057;
            --light: #fff0f6;
            --white: #ffffff;
            --success: #40c057;
            --warning: #f59f00;
            --danger: #fa5252;
            --shadow: 0 4px 6px rgba(214, 51, 132, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light);
            color: var(--text);
            line-height: 1.6;
        }

        .access-denied {
            background-color: #fff5f5;
            color: #c62828;
            padding: 20px;
            text-align: center;
            font-weight: bold;
            border-radius: 5px;
            margin: 20px;
            border-left: 5px solid #c62828;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #ffdeeb;
            margin-bottom: 30px;
        }

        .welcome-message {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .welcome-message span {
            font-weight: 600;
        }

        .nav-menu {
            display: flex;
            gap: 15px;
        }

        .nav-link {
            padding: 10px 20px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow);
        }

        .nav-link:hover {
            background: linear-gradient(to right, var(--primary-dark), #8c2cb3);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(166, 30, 77, 0.15);
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .card {
            background-color: var(--white);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid #ffdeeb;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(214, 51, 132, 0.15);
        }

        .card-header {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            color: var(--text);
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="welcome-message">Selamat datang, <span><?php echo $_SESSION['name']; ?></span></h1>
            <div class="nav-menu">
                <a href="matkul.php" class="nav-link">
                    <i class="fas fa-book"></i>
                    Kelola Mata Kuliah
                </a>
                <a href="upload_tugas.php" class="nav-link">
                    <i class="fas fa-upload"></i>
                    Upload Tugas
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
                    <i class="fas fa-chalkboard-teacher"></i> Aktivitas Terkini
                </div>
                <div class="card-body">
                    <p>Anda memiliki 3 tugas yang perlu diperiksa</p>
                    <p>2 mahasiswa mengirim pertanyaan</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt"></i> Jadwal Minggu Ini
                </div>
                <div class="card-body">
                    <p>Senin: Pemrograman Web (09:00 - 11:00)</p>
                    <p>Rabu: Basis Data (13:00 - 15:00)</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i> Statistik
                </div>
                <div class="card-body">
                    <p>Total Mata Kuliah: 4</p>
                    <p>Total Mahasiswa: 120</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>