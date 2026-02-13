<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice <?= $p->invoice_no ?></title>

<style>
body{
    font-family: "Segoe UI", Arial, sans-serif;
    font-size: 12px;
    background:#f8f9fc;
    color:#5a5c69;
}
.container{
    width:100%;
    padding:20px;
    background:#fff;
    border:1px solid #ddd;
}

/* SB ADMIN STYLE */
.text-primary{ color:#4e73df; }
.text-right{ text-align:right; }
.text-center{ text-align:center; }
.badge{
    padding:6px 12px;
    background:#1cc88a;
    color:#fff;
    font-weight:bold;
    font-size:12px;
    border-radius:4px;
}
h1,h2,h3{ margin:0; }

.header{
    border-bottom:2px solid #4e73df;
    margin-bottom:15px;
    padding-bottom:10px;
}
.company{
    font-size:14px;
    font-weight:bold;
    color:#4e73df;
}
.company small{
    display:block;
    font-size:11px;
    color:#858796;
}
.invoice-title{
    text-align:right;
}
.invoice-title h2{
    font-size:22px;
    color:#4e73df;
}
.invoice-meta{
    font-size:11px;
    color:#858796;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}
th{
    background:#4e73df;
    color:#fff;
    border:1px solid #ddd;
    padding:8px;
}
td{
    border:1px solid #ddd;
    padding:7px;
}

/* TOTAL */
.total-box{
    width:40%;
    float:right;
    margin-top:10px;
}
.total-box td{
    border:none;
    padding:5px;
}
.clear{ clear:both; }

.footer{
    margin-top:40px;
    text-align:center;
    font-size:11px;
    color:#858796;
}
</style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <table width="100%">
            <tr>
                <td width="60%">
                    <div class="company">
                        PT INTI SENTOSA BERSAMA
                        <small>
                        Harco Mangga Dua Blok B Lt.3 No.79<br>
                        Jakarta Pusat 10730
                        </small>
                    </div>
                </td>
                <td width="40%" class="invoice-title">
                    <h2>INVOICE</h2>
                    <div class="invoice-meta">
                        Tanggal : <?= date('d/m/Y') ?><br>
                        Invoice : <?= $p->invoice_no ?><br>
                        Sales : <?= $p->sales ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- STATUS -->
    <div style="margin-bottom:10px;">
        <span class="badge">PAID (CASH)</span>
    </div>

    <!-- BILL TO -->
    <div style="margin-bottom:15px;">
        <b class="text-primary">Bill To:</b><br>
        <?= $p->nama ?><br>
        <?= $p->alamat ?><br>
        <?= $p->kota ?><br>
        <?= $p->no_hp ?>
    </div>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Deskripsi</th>
                <th width="10%">Qty</th>
                <th width="20%">Harga</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Gacha <?= $p->event_name ?></td>
                <td class="text-center"><?= $p->qty_ticket ?></td>
                <td class="text-right"><?= number_format($p->ticket_price_manual) ?></td>
                <td class="text-right"><?= number_format($p->total_harga) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- TOTAL -->
    <table class="total-box">
        <tr>
            <td>Subtotal</td>
            <td class="text-right"><?= number_format($p->total_harga) ?></td>
        </tr>
        <tr>
            <td><b>Total</b></td>
            <td class="text-right"><b><?= number_format($p->total_harga) ?></b></td>
        </tr>
    </table>

    <div class="clear"></div>

    <!-- FOOTER -->
    <div class="footer">
        Terima kasih telah mengikuti event kami.<br>
        Invoice ini sah tanpa tanda tangan.
    </div>

</div>

</body>
</html>
