<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>{{ $voucher->voucher_no }}</title>
    <style>
        @page { margin: 34pt 40pt; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #111; line-height: 1.35; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; padding-bottom: 12pt; }
        .company { color: #800000; font-size: 15pt; font-weight: bold; }
        .title { color: #800000; font-size: 15pt; font-weight: bold; text-align: right; }
        .right { text-align: right; }
        .rule { border-top: 1pt solid #800000; margin-bottom: 14pt; }
        .meta td { width: 50%; padding: 0 18pt 12pt 0; vertical-align: top; }
        .meta strong { font-size: 10pt; }
        .items { margin-top: 8pt; }
        .items th { background: #800000; color: white; padding: 5pt 4pt; font-size: 8pt; text-align: center; }
        .items td { border: .5pt solid #bbb; padding: 5pt 4pt; vertical-align: top; }
        .items tr { page-break-inside: avoid; }
        .center { text-align: center; }
        .totals { margin-top: 4pt; }
        .totals td { padding: 4pt; font-weight: bold; }
        .grand { border-top: 1pt solid #111; border-bottom: 3pt double #111; }
        .notes { margin-top: 15pt; white-space: pre-line; }
        .signatures { margin-top: 34pt; page-break-inside: avoid; }
        .signatures td { width: 50%; padding-right: 20pt; vertical-align: top; }
        .signature { margin-top: 42pt; border-top: 1px dashed #333; padding-top: 5pt; }
    </style>
</head>
<body>
    <table class="header"><tr>
        <td><div class="company">{{ config('purchase_order.issuer_name') }}</div><div>{{ config('purchase_order.issuer_address') }}</div><div>{{ config('purchase_order.issuer_phone') }} | {{ config('purchase_order.issuer_email') }}</div></td>
        <td><div class="title">BAUCAR BAYARAN</div><div class="right"><strong>No. Dokumen: {{ $voucher->voucher_no }}</strong><br>Tarikh: {{ \Carbon\Carbon::parse($voucher->voucher_date)->locale('ms')->translatedFormat('d F Y') }}</div></td>
    </tr></table>
    <div class="rule"></div>
    <table class="meta"><tr>
        <td>Dibayar Kepada:<br><strong>{{ $supplierName }}</strong><br>{{ $quotation->supplier_address ?: '—' }}<br>{{ $quotation->supplier_phone ?: '—' }}</td>
        <td class="right"><strong>Cara Bayaran</strong><br>{{ $voucher->payment_method }}@if($voucher->payment_method === 'Cek')<br><span style="white-space: nowrap;">[ ] No Cek: {{ $chequeNo ?: '____________' }}</span>@endif</td>
    </tr></table>
    <table class="items">
        <thead><tr><th width="7%">BIL</th><th width="48%">BUTIRAN PEMBAYARAN / PAYMENT DETAILS</th><th width="20%">NO RUJUKAN</th><th width="25%">JUMLAH (RM)</th></tr></thead>
        <tbody>
            @foreach ($items as $item)
                <tr><td class="center">{{ $loop->iteration }}</td><td>{{ $item->item_description }}<br><small>{{ number_format((float) $item->quantity, 2) }} {{ $item->unit }} × RM {{ number_format((float) $item->unit_price, 2) }}</small></td><td class="center">{{ $quotation->quotation_no_supplier }}</td><td class="right">{{ number_format((float) $item->subtotal, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <table class="totals"><tr><td class="right">JUMLAH DIBAYAR (RM):</td><td width="25%" class="right grand">{{ number_format((float) $voucher->total_amount, 2) }}</td></tr></table>
    @if ($voucher->description)<div class="notes"><strong>Keterangan:</strong><br>{{ $voucher->description }}</div>@endif
    <table class="signatures"><tr><td><strong>Disediakan Oleh:</strong><div class="signature">@if($preparedBy){{ $preparedBy }}<br>@endif{{ $preparedRole }}</div></td><td><strong>Diterima Oleh:</strong><div class="signature">@if($receivedBy){{ $receivedBy }}<br>@endif{{ $receivedRole }}</div></td></tr></table>
</body>
</html>
