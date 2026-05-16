<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$pesan = '';
$tipe_pesan = 'success';
$mode = $_GET['mode'] ?? 'list';

// HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM jadwal WHERE id=$id");
    $pesan = '✅ Jadwal berhasil dihapus.';
    $mode = 'list';
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul'] ?? '');
    $layanan   = trim($_POST['layanan'] ?? '');
    $tgl_mulai = $_POST['tanggal_mulai'] ?? '';
    $tgl_selesai = $_POST['tanggal_selesai'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');
    $id        = (int)($_POST['id'] ?? 0);

    if (empty($judul) || empty($layanan) || empty($tgl_mulai) || empty($tgl_selesai)) {
        $pesan = '❌ Semua field wajib harus diisi!';
        $tipe_pesan = 'error';
    } elseif ($tgl_selesai < $tgl_mulai) {
        $pesan = '❌ Tanggal selesai tidak boleh sebelum tanggal mulai!';
        $tipe_pesan = 'error';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE jadwal SET judul=?, layanan=?, tanggal_mulai=?, tanggal_selesai=?, keterangan=? WHERE id=?");
            $stmt->bind_param('sssssi', $judul, $layanan, $tgl_mulai, $tgl_selesai, $keterangan, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO jadwal (judul, layanan, tanggal_mulai, tanggal_selesai, keterangan) VALUES (?,?,?,?,?)");
            $stmt->bind_param('sssss', $judul, $layanan, $tgl_mulai, $tgl_selesai, $keterangan);
        }
        if ($stmt->execute()) {
            $pesan = $id > 0 ? '✅ Jadwal berhasil diupdate!' : '✅ Jadwal berhasil ditambah!';
        } else {
            $pesan = '❌ Terjadi kesalahan database!';
            $tipe_pesan = 'error';
        }
        $mode = 'list';
    }
}

$edit_data = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_data = $conn->query("SELECT * FROM jadwal WHERE id=$id")->fetch_assoc();
}

// Ambil semua jadwal, filter bulan jika ada
$filter_bulan = $_GET['bulan'] ?? '';
if ($filter_bulan) {
    $stmt = $conn->prepare("SELECT * FROM jadwal WHERE DATE_FORMAT(tanggal_mulai, '%Y-%m') = ? ORDER BY tanggal_mulai ASC");
    $stmt->bind_param('s', $filter_bulan);
    $stmt->execute();
    $jadwal_list = $stmt->get_result();
} else {
    $jadwal_list = $conn->query("SELECT * FROM jadwal ORDER BY tanggal_mulai DESC");
}

// Jadwal bulan ini untuk kalender mini
$bulan_ini = date('Y-m');
$jadwal_bulan = $conn->query("SELECT * FROM jadwal WHERE DATE_FORMAT(tanggal_mulai, '%Y-%m') = '$bulan_ini' OR DATE_FORMAT(tanggal_selesai, '%Y-%m') = '$bulan_ini' ORDER BY tanggal_mulai ASC");
$jadwal_tanggal = [];
while ($j = $jadwal_bulan->fetch_assoc()) {
    $start = new DateTime($j['tanggal_mulai']);
    $end   = new DateTime($j['tanggal_selesai']);
    $end->modify('+1 day');
    $interval = new DateInterval('P1D');
    $range = new DatePeriod($start, $interval, $end);
    foreach ($range as $date) {
        $jadwal_tanggal[$date->format('Y-m-d')][] = $j['judul'];
    }
}

$layanan_options = ['Sewa GOR', 'Rental Tenda', 'Semua Layanan', 'Lainnya'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Kalender mini */
        .kalender {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .kalender h3 { color: #1f5b3a; margin-bottom: 15px; font-size: 1rem; }
        .kal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            font-size: 13px;
        }
        .kal-head {
            text-align: center;
            font-weight: bold;
            color: #1f5b3a;
            padding: 5px 2px;
            font-size: 11px;
        }
        .kal-hari {
            text-align: center;
            padding: 6px 2px;
            border-radius: 4px;
            cursor: default;
            min-height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .kal-hari.kosong { background: transparent; }
        .kal-hari.ada-jadwal {
            background: #ff9500;
            color: white;
            font-weight: bold;
            border-radius: 50%;
            cursor: pointer;
        }
        .kal-hari.hari-ini {
            border: 2px solid #1f5b3a;
            font-weight: bold;
        }
        .kal-hari.ada-jadwal.hari-ini {
            background: #1f5b3a;
            border-color: #ff9500;
        }
        .kal-hari:hover:not(.kosong) { opacity: 0.8; }

        /* Kartu jadwal */
        .jadwal-card {
            background: white;
            border-radius: 8px;
            padding: 15px 18px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-left: 4px solid #1f5b3a;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
        }
        .jadwal-card.lewat { border-left-color: #aaa; opacity: 0.7; }
        .jadwal-card.hari-ini-card { border-left-color: #ff9500; background: #fffbf2; }
        .jadwal-judul { font-weight: bold; color: #1f5b3a; font-size: 15px; margin-bottom: 4px; }
        .jadwal-info { color: #666; font-size: 13px; }
        .jadwal-tgl { font-size: 13px; color: #ff9500; font-weight: bold; margin-bottom: 4px; }
        .jadwal-aksi { display: flex; gap: 6px; align-items: center; }

        .stat-jadwal {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-j {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .stat-j .num { font-size: 1.8rem; font-weight: bold; color: #1f5b3a; }
        .stat-j .lbl { font-size: 12px; color: #888; margin-top: 3px; }
        .stat-j.orange .num { color: #ff9500; }
    </style>
</head>
<body class="admin-body">
<div class="admin-wrapper">
    <?php render_sidebar('manage_jadwal.php'); ?>
    <div class="main-content">
        <div class="top-bar">
            <h1>📆 Kelola Jadwal</h1>
            <a href="?mode=tambah" class="btn-sm btn-hijau" style="padding:8px 16px;">+ Tambah Jadwal</a>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe_pesan ?>" style="margin-bottom:15px;"><?= $pesan ?></div>
        <?php endif; ?>

        <!-- FORM TAMBAH / EDIT -->
        <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
        <div class="form-box" style="margin-bottom:20px;">
            <h2><?= $mode === 'edit' ? '✏️ Edit Jadwal' : '➕ Tambah Jadwal Baru' ?></h2>
            <form method="POST">
                <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Judul Kegiatan / Jadwal</label>
                    <input type="text" name="judul" value="<?= htmlspecialchars($edit_data['judul'] ?? '') ?>"
                           placeholder="Contoh: Acara Pernikahan, Maintenance GOR..." required>
                </div>
                <div class="form-group">
                    <label>Layanan Terkait</label>
                    <select name="layanan" required>
                        <option value="">-- Pilih Layanan --</option>
                        <?php foreach ($layanan_options as $lo): ?>
                        <option value="<?= $lo ?>" <?= ($edit_data['layanan'] ?? '') === $lo ? 'selected' : '' ?>><?= $lo ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai"
                               value="<?= $edit_data['tanggal_mulai'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai"
                               value="<?= $edit_data['tanggal_selesai'] ?? '' ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Keterangan <small style="color:#888;">(opsional)</small></label>
                    <textarea name="keterangan" rows="3"
                              placeholder="Tambahkan catatan tambahan jika diperlukan..."><?= htmlspecialchars($edit_data['keterangan'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-primary"><?= $mode === 'edit' ? '💾 Update Jadwal' : '➕ Tambah Jadwal' ?></button>
                <a href="manage_jadwal.php" style="margin-left:12px; color:#888; font-size:14px;">Batal</a>
            </form>
        </div>
        <?php endif; ?>

        <!-- STATISTIK -->
        <?php
        $total_jadwal   = $conn->query("SELECT COUNT(*) as c FROM jadwal")->fetch_assoc()['c'];
        $jadwal_aktif   = $conn->query("SELECT COUNT(*) as c FROM jadwal WHERE tanggal_selesai >= CURDATE()")->fetch_assoc()['c'];
        $jadwal_bulan_q = $conn->query("SELECT COUNT(*) as c FROM jadwal WHERE DATE_FORMAT(tanggal_mulai,'%Y-%m') = '" . date('Y-m') . "'")->fetch_assoc()['c'];
        ?>
        <div class="stat-jadwal">
            <div class="stat-j">
                <div class="num"><?= $total_jadwal ?></div>
                <div class="lbl">Total Jadwal</div>
            </div>
            <div class="stat-j orange">
                <div class="num"><?= $jadwal_aktif ?></div>
                <div class="lbl">Masih Aktif</div>
            </div>
            <div class="stat-j">
                <div class="num"><?= $jadwal_bulan_q ?></div>
                <div class="lbl">Bulan Ini</div>
            </div>
        </div>

        <!-- KALENDER MINI -->
        <div class="kalender">
            <h3>📅 Kalender <?= date('F Y') ?> <small style="color:#888; font-weight:normal;">(🟠 = ada jadwal)</small></h3>
            <div class="kal-grid">
                <?php foreach (['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h): ?>
                <div class="kal-head"><?= $h ?></div>
                <?php endforeach; ?>

                <?php
                $tgl_pertama = new DateTime(date('Y-m-01'));
                $day_of_week = (int)$tgl_pertama->format('w'); // 0=Sun
                // Padding kosong
                for ($i = 0; $i < $day_of_week; $i++): ?>
                <div class="kal-hari kosong"></div>
                <?php endfor;

                $hari_di_bulan = (int)date('t');
                $today = date('Y-m-d');
                for ($d = 1; $d <= $hari_di_bulan; $d++):
                    $tgl_str = date('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT);
                    $ada = isset($jadwal_tanggal[$tgl_str]);
                    $is_today = ($tgl_str === $today);
                    $kelas = 'kal-hari';
                    if ($ada) $kelas .= ' ada-jadwal';
                    if ($is_today) $kelas .= ' hari-ini';
                    $tooltip = $ada ? implode(', ', $jadwal_tanggal[$tgl_str]) : '';
                ?>
                <div class="<?= $kelas ?>" title="<?= htmlspecialchars($tooltip) ?>"><?= $d ?></div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- FILTER BULAN -->
        <div style="margin-bottom:15px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <form method="GET" style="display:flex; gap:8px; align-items:center;">
                <label style="font-size:14px; color:#555;">Filter Bulan:</label>
                <input type="month" name="bulan" value="<?= $filter_bulan ?>"
                       style="padding:6px 10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                <button type="submit" class="btn-sm btn-hijau" style="padding:7px 14px;">Tampilkan</button>
                <?php if ($filter_bulan): ?>
                <a href="manage_jadwal.php" class="btn-sm btn-orange" style="padding:7px 14px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- DAFTAR JADWAL -->
        <?php if ($jadwal_list->num_rows > 0):
            while ($j = $jadwal_list->fetch_assoc()):
                $is_lewat  = $j['tanggal_selesai'] < date('Y-m-d');
                $is_today  = ($j['tanggal_mulai'] <= date('Y-m-d') && $j['tanggal_selesai'] >= date('Y-m-d'));
                $kelas_card = 'jadwal-card';
                if ($is_lewat) $kelas_card .= ' lewat';
                elseif ($is_today) $kelas_card .= ' hari-ini-card';
        ?>
        <div class="<?= $kelas_card ?>">
            <div>
                <div class="jadwal-judul">
                    <?= $is_today ? '🟢 ' : ($is_lewat ? '⚫ ' : '🟡 ') ?>
                    <?= htmlspecialchars($j['judul']) ?>
                </div>
                <div class="jadwal-tgl">
                    📅 <?= date('d M Y', strtotime($j['tanggal_mulai'])) ?>
                    <?= $j['tanggal_mulai'] !== $j['tanggal_selesai'] ? ' — ' . date('d M Y', strtotime($j['tanggal_selesai'])) : '' ?>
                </div>
                <div class="jadwal-info">
                    🏷️ <?= htmlspecialchars($j['layanan']) ?>
                    <?php if ($j['keterangan']): ?>
                    &nbsp;·&nbsp; <?= htmlspecialchars(mb_substr($j['keterangan'], 0, 80)) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="jadwal-aksi">
                <?php if ($is_lewat): ?>
                <span style="font-size:12px; color:#aaa;">Selesai</span>
                <?php elseif ($is_today): ?>
                <span class="badge badge-confirmed">Berlangsung</span>
                <?php else: ?>
                <span class="badge badge-pending">Mendatang</span>
                <?php endif; ?>
                <a href="?mode=edit&id=<?= $j['id'] ?>" class="btn-sm btn-orange">✏</a>
                <a href="?aksi=hapus&id=<?= $j['id'] ?>" class="btn-sm btn-merah"
                   onclick="return confirm('Hapus jadwal ini?')">🗑</a>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div style="text-align:center; padding:40px; color:#999; background:white; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="font-size:48px; margin-bottom:10px;">📅</div>
            <p>Belum ada jadwal yang ditambahkan.</p>
            <a href="?mode=tambah" class="btn-primary" style="margin-top:15px; display:inline-block;">+ Tambah Jadwal Pertama</a>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
