<?php
include 'session.php';
include 'connect.php';

if ($_SESSION['role'] !== 'dosen') {
    $_SESSION['error'] = "Akses ditolak. Hanya dosen yang dapat mengelola mata kuliah.";
    header("Location: dashboard_dosen.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    
    if (empty($nama)) {
        $_SESSION['error'] = "Nama mata kuliah tidak boleh kosong";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO matkul (nama) VALUES (?)");
            $stmt->bind_param("s", $nama);
            $stmt->execute();
            
            $_SESSION['success'] = "Mata kuliah berhasil ditambahkan";
            header("Location: matkul.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Gagal menambahkan mata kuliah: " . $e->getMessage();
            error_log("Mata Kuliah Error: " . $e->getMessage());
        }
    }
}

if (isset($_GET['delete'])) {
    $id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    
    if ($id) {
        try {
            $stmt = $conn->prepare("DELETE FROM matkul WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $_SESSION['success'] = "Mata kuliah berhasil dihapus";
            header("Location: matkul.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Gagal menghapus mata kuliah: " . $e->getMessage();
            error_log("Delete Matkul Error: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = "ID mata kuliah tidak valid";
    }
}

$matkul = $conn->query("SELECT * FROM matkul ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mata Kuliah - E-Learning</title>
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
            --success: #40c057;
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
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 30px;
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 222, 235, 0.5);
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }

        h2 {
            font-size: 2rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        h2 i {
            color: var(--primary);
            font-size: 1.8rem;
        }

        .back-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .back-btn::before {
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

        .back-btn:hover::before {
            opacity: 1;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(166, 30, 77, 0.25);
        }

        .alert {
            padding: 16px 20px;
            margin-bottom: 30px;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background-color: #fff5f5;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert-success {
            background-color: #ebfbee;
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .add-form {
            background-color: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            border: 1px solid rgba(255, 222, 235, 0.5);
            position: relative;
            overflow: hidden;
        }

        .add-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }

        .form-group {
            margin-bottom: 20px;
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

        .btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            z-index: 1;
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

        .matkul-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 222, 235, 0.5);
        }

        .matkul-table th, 
        .matkul-table td {
            padding: 18px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .matkul-table th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .matkul-table tr:last-child td {
            border-bottom: none;
        }

        .matkul-table tr:hover {
            background-color: rgba(255, 222, 235, 0.1);
        }

        .action-link {
            color: var(--danger);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            background-color: rgba(250, 82, 82, 0.1);
        }

        .action-link:hover {
            background-color: rgba(250, 82, 82, 0.2);
            text-decoration: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            color: #666;
            border: 1px solid rgba(255, 222, 235, 0.5);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .empty-state p {
            font-size: 1.1rem;
        }

        @media (max-width: 992px) {
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .matkul-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 20px;
            }
            
            .header, .add-form {
                padding: 25px;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .matkul-table th, 
            .matkul-table td {
                padding: 14px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-book-open"></i> Kelola Mata Kuliah</h2>
            <a href="dashboard_dosen.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="add-form">
            <form method="POST">
                <div class="form-group">
                    <label for="nama">Tambah Mata Kuliah Baru</label>
                    <input type="text" id="nama" name="nama" class="form-control" 
                           placeholder="Masukkan nama mata kuliah" required>
                </div>
                <button type="submit" name="tambah" class="btn">
                    <i class="fas fa-plus"></i> Tambah Mata Kuliah
                </button>
            </form>
        </div>

        <?php if ($matkul->num_rows > 0): ?>
            <table class="matkul-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $matkul->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td>
                            <a href="?delete=<?= $row['id'] ?>" class="action-link"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <p>Belum ada mata kuliah yang terdaftar</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.action-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (!confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>