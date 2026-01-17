<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    die("Access denied");
}

$rental_id = $_GET['id'] ?? 0;

// Get rental data with product and user info
$stmt = $conn->prepare("
    SELECT s.*, p.nama_mesin, p.deskripsi,
           u.nama_lengkap as nama_penyewa, u.email, u.no_hp, u.alamat
    FROM sewa s
    JOIN produk p ON s.id_produk = p.id
    JOIN penyewa u ON s.id_penyewa = u.id
    WHERE s.id = ?
    GROUP BY s.id
");
$stmt->bind_param("i", $rental_id);
$stmt->execute();
$result = $stmt->get_result();
$rental = $result->fetch_assoc();

if (!$rental) {
    die("Data tidak ditemukan");
}

// Format tanggal
$tanggal_sewa = new DateTime($rental['tanggal_sewa']);
$tanggal_kembali = new DateTime($rental['tanggal_kembali']);
$periode = $tanggal_sewa->format('d F Y') . ' (masa sewa ' . $tanggal_sewa->diff($tanggal_kembali)->days + 1 . ' hari)';

// Generate nomor kwitansi
$nomor_kwitansi = sprintf("%04d/AT/KW/%s", $rental_id, date('Y'));

// Format jumlah dalam terbilang
function terbilang($angka) {
    $angka = abs($angka);
    $baca = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
    
    if ($angka < 12) return $baca[$angka];
    elseif ($angka < 20) return $baca[$angka - 10] . ' belas';
    elseif ($angka < 100) return $baca[floor($angka / 10)] . ' puluh ' . $baca[$angka % 10];
    elseif ($angka < 200) return 'seratus ' . terbilang($angka - 100);
    elseif ($angka < 1000) return $baca[floor($angka / 100)] . ' ratus ' . terbilang($angka % 100);
    elseif ($angka < 2000) return 'seribu ' . terbilang($angka - 1000);
    elseif ($angka < 1000000) return terbilang(floor($angka / 1000)) . ' ribu ' . terbilang($angka % 1000);
    elseif ($angka < 1000000000) return terbilang(floor($angka / 1000000)) . ' juta ' . terbilang($angka % 1000000);
    elseif ($angka < 1000000000000) return terbilang(floor($angka / 1000000000)) . ' milyar ' . terbilang($angka % 1000000000);
    else return terbilang(floor($angka / 1000000000000)) . ' triliun ' . terbilang($angka % 1000000000000);
}

$terbilang = ucwords(terbilang($rental['total_harga'])) . ' Rupiah';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .address {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .phone {
            font-size: 12px;
        }
        
        .content {
            margin: 30px 0;
        }
        
        .detail-row {
            margin-bottom: 15px;
            display: flex;
            align-items: baseline;
        }
        
        .label {
            font-weight: bold;
            min-width: 150px;
            margin-right: 5px;
            text-align: left;
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .label::after {
            content: ':';
            margin-left: 5px;
        }
        
        .value {
            flex: 1;
            font-size: 14px;
        }
        
        .amount-section {
            margin: 30px 0;
            text-align: center;
        }
        
        .amount-box {
            border: 2px solid #000;
            padding: 20px;
            display: inline-block;
            min-width: 300px;
        }
        
        .amount {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .terbilang {
            font-size: 12px;
            font-style: italic;
        }
        
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .location {
            text-align: center;
            flex: 1;
        }
        
        .signature {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="text-align: center;">
            <img src="/akasa_teknik/assets/images/logo.png" alt="AKASA Teknik" class="logo" onerror="this.style.display='none'">
            <div class="company-name">AKASA Teknik</div>
            <div class="address">Komp Merpati Kehutanan JL.Mahoni BB No. 9, Pabean Sedati Sidoarjo</div>
            <div class="phone">085854145578</div>
        </div>
    </div>
    
    <div class="content">
        <div class="detail-row">
            <div class="label">No Kwitansi</div>
            <div class="value"><?= $nomor_kwitansi ?></div>
        </div>
        
        <div class="detail-row">
            <div class="label">Sudah Terima Dari</div>
            <div class="value">
                <?= htmlspecialchars($rental['nama_penyewa']) ?>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="label">Uang Sejumlah</div>
            <div class="value"><?= $terbilang ?></div>
        </div>
        
        <div class="detail-row">
            <div class="label">Untuk Pembayaran</div>
            <div class="value">
                Biaya sewa <?= $rental['jumlah'] ?> unit <?= htmlspecialchars($rental['nama_mesin']) ?>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="label">Periode</div>
            <div class="value"><?= $periode ?></div>
        </div>
    </div>
    
    <div class="amount-section">
        <div class="amount-box">
            <div class="amount">Rp <?= number_format($rental['total_harga'], 0, ',', '.') ?>,-</div>
            <div class="terbilang"><?= $terbilang ?></div>
        </div>
    </div>
    
    <div class="footer">
        <div class="location">
            <strong>Sidoarjo</strong>, Tgl <?= date('d F Y') ?>
        </div>
        
        <div class="signature">
            <div class="signature-line">
                <img src="/akasa_teknik/assets/images/logo.png" alt="AKASA Teknik" style="width: 40px; height: 40px;" onerror="this.style.display='none'">
                <br>Abdul Kholik
            </div>
        </div>
    </div>
    
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Cetak</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</body>
</html>
