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
    <title>Review Jawaban - E-Learning</title>
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
            padding: 30px;
        }

        .container {
            max-width: 1400px;
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

        .submissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 222, 235, 0.5);
        }

        .submissions-table th, 
        .submissions-table td {
            padding: 18px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .submissions-table th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .submissions-table tr:last-child td {
            border-bottom: none;
        }

        .submissions-table tr:hover {
            background-color: rgba(255, 222, 235, 0.1);
        }

        .answer-content {
            max-width: 350px;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.5;
        }

        .grade-form {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .grade-input {
            width: 80px;
            padding: 10px 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #f8f9fa;
            transition: var(--transition);
        }

        .grade-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(214, 51, 132, 0.15);
            background-color: var(--white);
        }

        .comment-input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            min-width: 200px;
            font-size: 0.95rem;
            background-color: #f8f9fa;
            transition: var(--transition);
        }

        .comment-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(214, 51, 132, 0.15);
            background-color: var(--white);
        }

        .submit-btn {
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .submit-btn::before {
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

        .submit-btn:hover::before {
            opacity: 1;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(166, 30, 77, 0.2);
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

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: rgba(64, 192, 87, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(245, 159, 0, 0.1);
            color: var(--warning);
        }

        @media (max-width: 992px) {
            .container {
                padding: 20px;
            }
            
            .header {
                padding: 25px;
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .submissions-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 768px) {
            .grade-form {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .comment-input {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .header {
                padding: 20px;
            }
            
            .submissions-table th, 
            .submissions-table td {
                padding: 12px 15px;
            }
        }
    </style>
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
                        <td>
                            <?php if (isset($row['nilai'])): ?>
                                <span class="badge badge-success"><?= $row['nilai'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-warning">Belum dinilai</span>
                            <?php endif; ?>
                        </td>
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
                <i class="fas fa-inbox"></i>
                <p>Belum ada jawaban yang dikumpulkan mahasiswa</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>