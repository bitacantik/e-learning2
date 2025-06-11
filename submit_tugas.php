<?php
include 'session.php';
include 'connect.php';

if ($_SESSION['role'] !== 'mahasiswa') {
    $_SESSION['error'] = "Akses ditolak. Hanya mahasiswa yang dapat mengumpulkan tugas.";
    header("Location: dashboard_mahasiswa.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tugas_id = filter_input(INPUT_POST, 'tugas_id', FILTER_VALIDATE_INT);
    $jawaban = trim($_POST['jawaban']);
    $user_id = $_SESSION['user_id'];

    if (!$tugas_id || $tugas_id < 1) {
        $error = "Pilih tugas yang valid";
    } elseif (empty($jawaban)) {
        $error = "Jawaban tidak boleh kosong";
    } else {
        try {
            $checkStmt = $conn->prepare("SELECT id FROM submissions WHERE tugas_id = ? AND user_id = ?");
            $checkStmt->bind_param("ii", $tugas_id, $user_id);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows > 0) {
                $error = "Anda sudah mengumpulkan tugas ini";
            } else {
                $stmt = $conn->prepare("INSERT INTO submissions (tugas_id, user_id, jawaban) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $tugas_id, $user_id, $jawaban);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Tugas berhasil dikumpulkan!";
                    header("Location: dashboard_mahasiswa.php");
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Submission Error: " . $e->getMessage());
            $error = "Terjadi kesalahan saat mengumpulkan tugas";
        }
    }
}

$tugas = $conn->query("SELECT * FROM tugas WHERE deadline > NOW() ORDER BY deadline ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulkan Tugas - E-Learning</title>
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
            min-height: 100vh;
            padding: 30px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 25px 30px;
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
            font-size: 1.8rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        h2 i {
            color: var(--primary);
            font-size: 1.5rem;
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

        .submission-form {
            background-color: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 222, 235, 0.5);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .submission-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--text);
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

        .select-assignment {
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

        .select-assignment:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(214, 51, 132, 0.15);
            background-color: var(--white);
        }

        .textarea-answer {
            min-height: 250px;
            resize: vertical;
        }

        .assignment-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .assignment-info i {
            color: var(--primary);
        }

        .submit-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
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
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(166, 30, 77, 0.25);
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
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
                padding: 25px;
            }
            
            .submission-form {
                padding: 30px;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 20px;
            }
            
            .submission-form {
                padding: 25px 20px;
            }
            
            .back-btn, .submit-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-file-upload"></i> Kumpulkan Tugas</h2>
            <a href="dashboard_mahasiswa.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali
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

        <?php if ($tugas->num_rows > 0): ?>
            <div class="submission-form">
                <form method="POST">
                    <div class="form-group">
                        <label for="tugas_id">Pilih Tugas</label>
                        <select id="tugas_id" name="tugas_id" class="select-assignment" required>
                            <option value="">-- Pilih Tugas --</option>
                            <?php while ($t = $tugas->fetch_assoc()): ?>
                                <option value="<?= $t['id'] ?>">
                                    <?= htmlspecialchars($t['judul']) ?> 
                                    (Batas: <?= date('d M Y', strtotime($t['deadline'])) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jawaban">Jawaban Anda</label>
                        <textarea id="jawaban" name="jawaban" class="form-control textarea-answer" 
                                  placeholder="Tulis jawaban Anda disini..." required></textarea>
                        <div class="assignment-info">
                            <i class="fas fa-info-circle"></i> Pastikan jawaban sudah lengkap sebelum mengumpulkan
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Kumpulkan Tugas
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-check"></i>
                <p>Tidak ada tugas yang aktif saat ini</p>
                <p>Semua tugas telah melewati batas pengumpulan</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>