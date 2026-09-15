<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $tutor->nama_lengkap }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.4; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0b5ed7; padding-bottom: 12px; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 18px; color: #0b5ed7; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 11px; color: #64748b; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 16px; text-decoration: underline; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .section-header { font-size: 12px; font-weight: bold; color: #0f172a; margin: 16px 0 8px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-data th { background: #f1f5f9; color: #334155; font-weight: bold; padding: 8px; border: 1px solid #cbd5e1; text-align: left; }
        .table-data td { padding: 8px; border: 1px solid #cbd5e1; }
        .total-box { background: #ecfdf5; border: 1px solid #10b981; padding: 12px; border-radius: 6px; text-align: right; margin-bottom: 30px; }
        .total-box .amount { font-size: 18px; font-weight: bold; color: #047857; }
        .signature-table { width: 100%; margin-top: 40px; }
        .signature-table td { text-align: center; vertical-align: top; width: 50%; }
        .signature-space { height: 60px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PKBM PIKAT</h2>
        <p>Pusat Kegiatan Belajar Masyarakat Pikat • Sistem Presensi & Payroll Digital</p>
        <p>Alamat: Sleman, D.I. Yogyakarta • Telp: (0274) 123456</p>
    </div>

    <div class="title">SLIP GAJI & HONORARIUM TUTOR</div>

    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Nama Tutor</strong></td>
            <td style="width: 35%;">: {{ $tutor->nama_lengkap }}</td>
            <td style="width: 15%;"><strong>Periode</strong></td>
            <td style="width: 35%;">: {{ $payroll['periode_label'] }}</td>
        </tr>
        <tr>
            <td><strong>NIK / NIP</strong></td>
            <td>: {{ $tutor->nik }}</td>
            <td><strong>Tgl Cetak</strong></td>
            <td>: {{ $tanggalCetak->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
    </table>

    <div class="section-header">1. Rincian Honorarium Pembelajaran Tutorial Sesuai SK</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Siswa</th>
                <th style="width: 15%; text-align: center;">Status</th>
                <th style="width: 15%; text-align: center;">Total Sesi</th>
                <th style="width: 15%; text-align: center;">Total Jam</th>
                <th style="width: 20%; text-align: right;">Subtotal Honor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payroll['siswa_summary'] as $idx => $s)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td><strong>{{ $s['nama_siswa'] }}</strong></td>
                <td style="text-align: center;">{{ $s['is_abk_label'] ?? ($s['is_abk'] ? 'ABK' : 'Reguler') }}</td>
                <td style="text-align: center;">{{ $s['total_sesi'] }} pertemuan</td>
                <td style="text-align: center;">{{ $s['total_jam'] }} Jam</td>
                <td style="text-align: right; font-weight: bold; color: #065f46;">{{ $s['formatted_subtotal'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #94a3b8;">Tidak ada data sesi mengajar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-box">
        <span style="font-size: 13px; font-weight: bold; color: #065f46;">TOTAL TAKE HOME PAY (HONORARIUM):</span><br>
        <span class="amount">{{ $payroll['formatted_total_honor'] }}</span>
    </div>

    <table class="signature-table">
        <tr>
            <td>
                Penerima (Tutor),
                <div class="signature-space"></div>
                <strong>({{ $tutor->nama_lengkap }})</strong>
            </td>
            <td>
                Sleman, {{ $tanggalCetak->translatedFormat('d F Y') }}<br>
                Kepala Sekolah PKBM Pikat,
                <div class="signature-space"></div>
                <strong>( Kepala Sekolah )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
