<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'NM Gallery' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10px;
            margin: 20px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #C9A84C;
        }
        .header h1 { color: #C9A84C; font-size: 22px; }
        .header h3 { font-size: 12px; color: #555; margin: 5px 0; }
        .header .periode { font-size: 9px; color: #888; margin-top: 5px; }
        .title { font-size: 14px; font-weight: bold; text-align: center; margin: 15px 0; }
        
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #C9A84C; color: white; padding: 8px; text-align: center; font-size: 9px; border: 1px solid #b8963a; }
        td { border: 1px solid #ddd; padding: 6px; font-size: 9px; vertical-align: top; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .summary-box { margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
        .badge-out { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; }
        .badge-success { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 4px; }
        
        .struk-header { text-align: center; margin-bottom: 20px; }
        .info-table td { border: none; padding: 4px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 6px; }
        .struk-footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 8px; }
        
        /* PDF Transaksi Styles */
.nota-wrapper {
    max-width: 100%;
    margin: 0 auto;
}
.nota-header {
    background: #0a0a0a;
    padding: 16px 20px;
    text-align: center;
    margin-bottom: 20px;
    border-radius: 8px 8px 0 0;
}
.nota-brand {
    font-size: 22px;
    font-weight: bold;
    color: #e0c06e;
    letter-spacing: 1px;
}
.nota-tagline {
    font-size: 9px;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    margin-top: 4px;
}
.nota-trxid {
    margin-top: 12px;
}
.nota-trxid .label {
    font-size: 8px;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
}
.nota-trxid .num {
    font-family: monospace;
    font-size: 14px;
    color: #e0c06e;
    font-weight: bold;
}
.nota-trxid .date {
    font-size: 8px;
    color: rgba(255,255,255,0.3);
}
.nota-cust-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #ddd;
}
.nota-field-k {
    font-size: 8px;
    text-transform: uppercase;
    color: #999;
    font-weight: bold;
}
.nota-field-v {
    font-size: 11px;
    color: #333;
    font-weight: 500;
    margin-top: 3px;
}
.nota-items-tbl {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
}
.nota-items-tbl th {
    background: none;
    color: #888;
    font-size: 9px;
    text-transform: uppercase;
    padding: 8px 4px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.nota-items-tbl td {
    padding: 8px 4px;
    font-size: 10px;
    border-bottom: 1px solid #eee;
}
.nota-foot {
    text-align: center;
    font-size: 9px;
    color: #999;
    padding-top: 12px;
    border-top: 1px dashed #ddd;
    margin-top: 15px;
}
.nota-signature {
    margin-top: 30px;
    text-align: center;
}
.sign-line {
    border-top: 1px solid #ddd;
    width: 150px;
    margin: 20px auto 8px;
}
.sign-text {
    font-size: 9px;
    color: #666;
}
.gold {
    color: #C9A84C;
    font-weight: bold;
}
.grand-total {
    font-size: 12px;
}
.badge-out {
    background: #fff3cd;
    color: #856404;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 9px;
    display: inline-block;
}
.badge-success {
    background: #d4edda;
    color: #155724;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 9px;
    display: inline-block;
}
.badge-danger {
    background: #f8d7da;
    color: #721c24;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 9px;
    display: inline-block;
}
.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}
/* PDF Transaksi Styles */
.modal-nota {
    margin: 0;
    border: none;
    padding: 0;
}
.modal-nota-top {
    background: #0a0a0a;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
}
.modal-nota-brand {
    font-family: 'Instrument Serif', serif;
    font-style: italic;
    font-size: 18px;
    color: #e0c06e;
}
.modal-nota-tagline {
    font-size: 8px;
    color: rgba(255,255,255,0.4);
}
.modal-nota-addr {
    font-size: 7px;
    color: rgba(255,255,255,0.3);
    margin-top: 4px;
}
.modal-nota-trxid {
    text-align: right;
}
.modal-nota-trxid .label {
    font-size: 7px;
    color: rgba(255,255,255,0.4);
}
.modal-nota-trxid .num {
    font-family: monospace;
    font-size: 12px;
    color: #e0c06e;
    font-weight: bold;
}
.modal-nota-trxid .date {
    font-size: 7px;
    color: rgba(255,255,255,0.3);
}
.nota-cust-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #ddd;
}
.nota-field-k {
    font-size: 7px;
    text-transform: uppercase;
    color: #999;
    font-weight: bold;
}
.nota-field-v {
    font-size: 11px;
    color: #333;
    font-weight: 500;
    margin-top: 2px;
}
.nota-items-tbl {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}
.nota-items-tbl th {
    background: none;
    color: #888;
    font-size: 8px;
    text-transform: uppercase;
    padding: 6px 0;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.nota-items-tbl td {
    padding: 6px 0;
    font-size: 10px;
    border-bottom: 1px solid #eee;
}
.nota-totals {
    background: #fafafa;
    border: 1px solid #e0c06e;
    border-radius: 8px;
    padding: 10px 12px;
    margin: 12px 0;
}
.nota-tot-row {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    padding: 3px 0;
}
.nota-tot-row.grand {
    border-top: 1px solid #ddd;
    margin-top: 5px;
    padding-top: 8px;
    font-weight: bold;
}
.nota-tot-lbl {
    color: #666;
}
.nota-tot-val {
    font-family: monospace;
    color: #C9A84C;
}
.grand-total {
    font-size: 11px;
    font-weight: bold;
}
.nota-foot {
    text-align: center;
    font-size: 8px;
    color: #999;
    padding-top: 10px;
    border-top: 1px dashed #ddd;
    margin-top: 10px;
}
.gold {
    color: #C9A84C;
    font-weight: bold;
}
.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NM GALLERY</h1>
        <h3>Baju Bodo Collection</h3>
        <div class="periode">Tanete, Kec. Bulukumpa, Kabupaten Bulukumba</div>
        <div class="periode">Cetak: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <div class="title">{{ $title ?? 'LAPORAN' }}</div>

    @yield('content')

    <div class="footer">
        Dicetak dari NM Gallery System | Terima kasih
    </div>
</body>
</html>