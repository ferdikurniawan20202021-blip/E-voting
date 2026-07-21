<?php session_start(); if(isset($_SESSION['level'])) header("Location: ../index.php"); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Voting Ketua OSIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #1E3A8A;   /* Navy */
            --secondary: #3B82F6; /* Biru Terang */
            --accent: #F59E0B;    /* Emas */
            --text-dark: #1F2937;
            --text-light: #64748b;
        }

        body { 
            font-family: 'Poppins', sans-serif;
            background: url('../assets/img/login-bg.png') no-repeat center center fixed; 
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to right, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.3) 100%);
            z-index: 1;
        }

        .main-container {
            position: relative;
            z-index: 2;
            display: flex;
            width: 100%;
            max-width: 1300px;
            padding: 0 5%;
            justify-content: space-between;
            align-items: center;
            gap: 50px;
        }

        /* ====== PANEL KIRI ====== */
        .left-content { flex: 1; color: white; }
        .hero-title { font-size: 3.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 20px; letter-spacing: -1px; text-shadow: 0 4px 15px rgba(0,0,0,0.6); }
        .hero-title span { color: var(--secondary); text-shadow: 0 4px 15px rgba(0,0,0,0.6); }
        .hero-desc { font-size: 1.15rem; color: #f1f5f9; margin-bottom: 40px; max-width: 600px; line-height: 1.6; font-weight: 400; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }

        .features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 2rem; max-width: 600px; }
        .feature-item { text-align: center; }
        .feature-icon-wrapper {
            width: 60px; height: 60px; background-color: rgba(255, 255, 255, 0.15); backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.3); border-radius: 50%; display: inline-flex; align-items: center;
            justify-content: center; margin-bottom: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); transition: 0.3s;
        }
        .feature-item:hover .feature-icon-wrapper { background-color: var(--secondary); border-color: var(--secondary); transform: translateY(-5px); }
        .feature-icon { font-size: 1.5rem; color: #ffffff; }
        .feature-title { font-weight: 700; font-size: 0.85rem; letter-spacing: 1px; color: #ffffff; margin-bottom: 5px; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
        .feature-desc { font-size: 0.7rem; color: #cbd5e1; line-height: 1.4; text-shadow: 0 1px 3px rgba(0,0,0,0.5); }

        .cta-badge { background-color: var(--accent); color: #451a03; padding: 12px 30px; border-radius: 50px; display: inline-flex; align-items: center; font-weight: 700; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4); }
        .cta-badge i { font-size: 1.3rem; margin-right: 12px; background: rgba(0,0,0,0.1); padding: 5px 8px; border-radius: 8px; }
        .cta-text-main { display: block; font-size: 0.95rem; letter-spacing: 0.5px; }
        .cta-text-sub { display: block; font-size: 0.75rem; font-weight: 500; opacity: 0.8; }

        /* ====== PANEL KANAN ====== */
        /* Styling Modern & Bersih */
.login-card { 
    width: 100%;
    max-width: 430px;
    padding: 40px; 
    border-radius: 28px; 
    background: rgba(255, 255, 255, 0.95); 
    backdrop-filter: blur(20px);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.8);
    position: relative;
    color: var(--text-dark);
}

.form-label { 
    font-size: 0.75rem; 
    font-weight: 700; 
    color: #475569; 
    margin-bottom: 10px; 
    text-transform: uppercase; 
    letter-spacing: 0.8px; 
}

/* Input yang lebih modern dengan efek fokus */
.input-group {
    background: #f1f5f9;
    border-radius: 14px;
    border: 2px solid transparent;
    padding: 2px;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    margin-bottom: 20px;
}

.input-group:focus-within {
    background: #ffffff;
    border-color: var(--secondary);
    box-shadow: 0 8px 16px -4px rgba(59, 130, 246, 0.2);
}

.input-group-text {
    background: transparent;
    border: none;
    color: #94a3b8;
    padding: 0 15px;
    font-size: 1.1rem;
}

.form-control {
    background: transparent !important;
    border: none !important;
    padding: 14px 10px;
    font-size: 0.95rem;
    color: var(--text-dark) !important;
    font-weight: 500;
}

.form-control:focus { outline: none !important; }

/* Tombol Login yang lebih menonjol */
.btn-login { 
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border-radius: 14px;
    padding: 15px;
    font-weight: 700;
    border: none;
    transition: all 0.4s ease;
    width: 100%;
    margin-top: 10px;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.btn-login:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(37, 99, 235, 0.4);
    filter: brightness(1.1);
}

/* Tombol Kembali yang Minimalis */
.btn-back {
    display: block;
    text-align: center;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 20px;
    text-decoration: none;
    transition: 0.3s;
}
.btn-back:hover { color: var(--primary); }

        .voting-badge { position: absolute; top: -16px; left: 50%; transform: translateX(-50%); background: var(--secondary); color: #ffffff; padding: 6px 20px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4); }

        .logo-img { width: 65px; margin-bottom: 10px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); } /* Margin bawah dikurangi */
        .header-text { color: var(--primary); font-weight: 800; font-size: 1.25rem; margin-bottom: 2px; letter-spacing: 0.5px; }
        .subtitle-text { color: var(--text-light); font-size: 0.8rem; font-weight: 500; letter-spacing: 1px; margin-bottom: 20px; } /* Margin bawah dikurangi */
        
        .form-label { font-size: 0.75rem; font-weight: 600; color: var(--text-dark); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group, .select-wrapper { background: #ffffff; border-radius: 12px; border: 1px solid #cbd5e1; margin-bottom: 15px; transition: all 0.3s ease; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); } /* Margin bawah dikurangi */
        .input-group:focus-within, .select-wrapper:focus-within { border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .input-group-text { background: transparent; border: none; color: #94a3b8; padding-left: 15px; font-size: 1rem; }
        
        /* Tinggi kolom input dirampingkan sedikit */
        .form-control { background: transparent !important; border: none !important; color: var(--text-dark) !important; padding: 10px 15px 10px 10px; font-size: 0.9rem; box-shadow: none !important; }
        .form-control::placeholder { color: #94a3b8; font-weight: 400; }
        .form-select { background-color: transparent !important; border: none !important; color: var(--text-dark) !important; font-size: 0.9rem; padding: 10px 15px; box-shadow: none !important; cursor: pointer; }
        
        input:-webkit-autofill { -webkit-box-shadow: 0 0 0 30px #ffffff inset !important; -webkit-text-fill-color: var(--text-dark) !important; transition: background-color 5000s ease-in-out 0s; }
        
        /* Tombol Aksi */
        .btn-login { background-color: var(--primary); color: #ffffff; border-radius: 12px; font-weight: 700; padding: 12px; border: none; transition: all 0.3s ease; font-size: 0.95rem; letter-spacing: 0.5px; margin-top: 5px; box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3); } /* Padding & margin dikurangi */
        .btn-login:hover { background-color: var(--secondary); color: white; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4); }
        
        .btn-back { display: block; text-align: center; background-color: transparent; color: var(--text-light); border: 1px solid #cbd5e1; border-radius: 12px; font-weight: 600; padding: 10px; font-size: 0.85rem; margin-top: 12px; text-decoration: none; transition: all 0.3s ease; } /* Padding & margin dikurangi */
        .btn-back:hover { background-color: #f1f5f9; color: var(--text-dark); border-color: #94a3b8; }

        @media (max-width: 991px) {
            body::before { background: rgba(15, 23, 42, 0.75); }
            .main-container { flex-direction: column; justify-content: center; text-align: center; padding: 40px 20px; }
            .left-content { margin-bottom: 30px; }
            .hero-title { font-size: 2.5rem; }
            .features-grid { grid-template-columns: repeat(2, 1fr); margin: 0 auto 2rem; }
            .cta-badge { margin: 0 auto; }
            .login-card { margin: 0 auto; background: rgba(255, 255, 255, 0.95); }
        }
    </style>
</head>
<body>

<div class="main-container">
    
    <div class="left-content">
        <h1 class="hero-title">Wujudkan<br><span>Demokrasi Digital</span></h1>
        <p class="hero-desc">
            Suara Anda menentukan masa depan sekolah kita. Gunakan hak pilih Anda melalui sistem pemilihan elektronik yang modern, akurat, dan terpercaya.
        </p>
        
        <div class="features-grid d-none d-md-grid">
            <div class="feature-item">
                <div class="feature-icon-wrapper"><i class="bi bi-shield-lock-fill feature-icon"></i></div>
                <div class="feature-title">AMAN</div>
                <div class="feature-desc">Data & pilihan<br>Anda terlindungi</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper"><i class="bi bi-check-circle-fill feature-icon"></i></div>
                <div class="feature-title">JUJUR</div>
                <div class="feature-desc">Satu siswa,<br>satu suara</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper"><i class="bi bi-lightning-charge-fill feature-icon"></i></div>
                <div class="feature-title">MUDAH</div>
                <div class="feature-desc">Proses voting cepat<br>dan online</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper"><i class="bi bi-bar-chart-fill feature-icon"></i></div>
                <div class="feature-title">TRANSPARAN</div>
                <div class="feature-desc">Hasil real-time<br>terpercaya</div>
            </div>
        </div>

        <div class="cta-badge d-none d-md-inline-flex">
            <i class="bi bi-envelope-paper-heart"></i>
            <div class="text-start">
                <span class="cta-text-main">AYO GUNAKAN HAK PILIHMU!</span>
                <span class="cta-text-sub">Pilih Pemimpin, Wujudkan Perubahan</span>
            </div>
        </div>
    </div>

    <div class="login-card">
        <div class="voting-badge"><i class="bi bi-shield-check me-1"></i> Secure Vote</div>
        
        <div class="text-center mt-2">
            <img src="../assets/img/logo.png" alt="Logo" class="logo-img">
            <h5 class="header-text">SMK N 1 TANJUNG RAYA</h5>
            <p class="subtitle-text">E-VOTING KETUA OSIS</p>
        </div>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger text-center bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-2 mb-3" style="font-size: 0.8rem; border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST" autocomplete="off">
            <label class="form-label">Akses Sebagai</label>
            <div class="select-wrapper">
                <select class="form-select" name="level" required>
                    <option value="siswa">Siswa (Pemilih)</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <label class="form-label">Username / NIS</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                <input type="text" name="username" class="form-control" required placeholder="Masukkan NIS / Username" autocomplete="nope">
            </div>

            <label class="form-label">Password</label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" required placeholder="Masukkan Password" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-login w-100">
                MASUK SEKARANG <i class="bi bi-box-arrow-in-right ms-1"></i>
            </button>
            
            <a href="../welcome.php" class="btn-back w-100">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Utama
            </a>
        </form>
    </div>

</div>

</body>
</html>