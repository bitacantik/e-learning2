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
    <title>Kelola Mata Kuliah - Classroom</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ffdeeb;
        }

        h2 {
            color: var(--primary);
            font-size: 1.8rem;
        }

        .back-btn {
            padding: 10px 20px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: linear-gradient(to right, var(--primary-dark), #8c2cb3);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
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
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border: 1px solid #ffdeeb;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ffdeeb;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(214, 51, 132, 0.2);
        }

        .btn {
            padding: 12px 24px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background: linear-gradient(to right, var(--primary-dark), #8c2cb3);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .matkul-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ffdeeb;
        }

        .matkul-table th, 
        .matkul-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .matkul-table th {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: var(--white);
            font-weight: 600;
        }

        .matkul-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .action-link {
            color: var(--danger);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            border: 1px solid #ffdeeb;
        }

        @media (max-width: 768px) {
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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Kelola Mata Kuliah</h2>
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