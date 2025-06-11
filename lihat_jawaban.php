<?php
include 'session.php';
include 'connect.php';

if ($_SESSION['role'] !== 'dosen') {
    $_SESSION['error'] = "Akses ditolak. Hanya dosen yang dapat melihat jawaban mahasiswa.";
    header("Location: dashboard_dosen.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub_id'])) {
    $nilai = filter_input(INPUT_POST, 'nilai', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 100]
    ]);
    $komentar = htmlspecialchars(trim($_POST['komentar']));
    $sub_id = filter_input(INPUT_POST, 'sub_id', FILTER_VALIDATE_INT);

    if ($nilai === false || $sub_id === false) {
        $_SESSION['error'] = "Nilai harus antara 0-100 dan ID harus valid";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE submissions SET nilai = ?, komentar = ? WHERE id = ?");
            $stmt->bind_param("isi", $nilai, $komentar, $sub_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = "Penilaian berhasil disimpan!";
            }
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['error'] = "Gagal menyimpan penilaian: " . $e->getMessage();
            error_log("Penilaian Error: " . $e->getMessage());
        }
    }
    header("Location: lihat_jawaban.php");
    exit();
}

$sql = "SELECT s.id, u.name AS mahasiswa, t.judul AS tugas, s.jawaban, s.waktu, s.nilai, s.komentar
        FROM submissions s
        JOIN users u ON s.user_id = u.id
        JOIN tugas t ON s.tugas_id = t.id
        ORDER BY s.waktu DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Jawaban - Classroom</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
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

        .submissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ffdeeb;
        }

        .submissions-table th, 
        .submissions-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .submissions-table th {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: var(--white);
            font-weight: 600;
        }

        .submissions-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .answer-content {
            max-width: 300px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .grade-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .grade-input {
            width: 70px;
            padding: 8px 12px;
            border: 1px solid #ffdeeb;
            border-radius: 4px;
        }

        .comment-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ffdeeb;
            border-radius: 4px;
            min-width: 150px;
        }

        .submit-btn {
            padding: 8px 16px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }

        .submit-btn:hover {
            background: linear-gradient(to right, var(--primary-dark), #8c2cb3);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            .submissions-table {
                display: block;
                overflow-x: auto;
            }
            
            .grade-form {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-history"></i> Review Jawaban Mahasiswa</h2>
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

        <?php if ($result->num_rows > 0): ?>
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Tugas</th>
                        <th>Jawaban</th>
                        <th>Waktu Pengumpulan</th>
                        <th>Nilai</th>
                        <th>Komentar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['mahasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['tugas']) ?></td>
                        <td class="answer-content"><?= nl2br(htmlspecialchars($row['jawaban'])) ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['waktu'])) ?></td>
                        <td><?= $row['nilai'] ?? 'Belum dinilai' ?></td>
                        <td><?= $row['komentar'] ? nl2br(htmlspecialchars($row['komentar'])) : '-' ?></td>
                        <td>
                            <form method="POST" class="grade-form">
                                <input type="hidden" name="sub_id" value="<?= $row['id'] ?>">
                                <input type="number" name="nilai" class="grade-input" min="0" max="100" 
                                       value="<?= $row['nilai'] ?? '' ?>" placeholder="Nilai" required>
                                <input type="text" name="komentar" class="comment-input" 
                                       value="<?= htmlspecialchars($row['komentar'] ?? '') ?>" placeholder="Komentar">
                                <button type="submit" class="submit-btn">Simpan</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <p><i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 15px;"></i></p>
                <p>Belum ada jawaban yang dikumpulkan mahasiswa</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>