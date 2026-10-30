<?php
// session_start();

// if($_SESSION['role'] != "user"){
//     header("location:../login.php");
// }
?>

<!DOCTYPE html>
<html>
<head>

    <title>Dashboard User</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
    .feedback-btn { display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;background:#e85d04;color:#fff;text-decoration:none;font-weight:600; }
    .feedback-modal .modal-box{width:520px}
    /* Modal base styles (same behavior as admin) */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; align-items: center; justify-content: center; }
    .modal-overlay.show { display:flex; }
    .modal-box { background: #fff; border-radius: 16px; padding: 28px; width: 460px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .modal-box h3 { font-size:16px; font-weight:700; margin-bottom:18px; color:#1a1f2e; }
    .modal-actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
    </style>

</head>
<body>

<div class="navbar">

    <span class="logo">
        Dashboard User
    </span>

    <div class="menu">
        <a href="#" class="feedback-btn" onclick="document.getElementById('user-feedback').classList.add('show');return false;">✉ Kritik & Saran</a>
        <a href="../logout.php">Logout</a>
    </div>

</div>

<div class="container">

    <div class="row">

        <div class="card">
            <h2>
                <a href="booking.php">
                    Booking Lapangan
                </a>
            </h2>
        </div>

        <div class="card">
            <h2>
                <a href="jadwal.php">
                    Jadwal Bermain
                </a>
            </h2>
        </div>

        <div class="card">
            <h2>
                <a href="transaksi.php">
                    Transaksi
                </a>
            </h2>
        </div>

    </div>

</div>

<!-- Modal Kritik & Saran (user) -->
<div class="modal-overlay feedback-modal" id="user-feedback">
    <div class="modal-box">
        <h3>&#9993; Kritik & Saran</h3>
        <form id="user-feedback-form">
            <div class="form-group"><label>Nama (opsional)</label><input type="text" name="fb_name" class="form-control"></div>
            <div class="form-group"><label>Pesan</label><textarea name="fb_msg" class="form-control" required style="min-height:120px;"></textarea></div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                <button type="button" class="btn-action btn-secondary" onclick="document.getElementById('user-feedback').classList.remove('show')">Batal</button>
                <button type="submit" class="btn-action btn-primary">Kirim</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('user-feedback-form').addEventListener('submit', function(e){
    e.preventDefault();
    const data = new FormData(this);
    fetch('../kirim_feedback.php',{method:'POST',body:data}).then(r=>r.json()).then(res=>{
        if(res.success){ alert('Terima kasih, pesan Anda telah terkirim.'); this.reset(); document.getElementById('user-feedback').classList.remove('show'); }
        else alert('Gagal: '+(res.error||'unknown'));
    }).catch(()=>alert('Gagal mengirim pesan'));
});
</script>

</body>
</html>