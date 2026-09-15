<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>{{ $order->po_no }}</title>
    <style>
        @page { margin: 40pt 42pt; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #000; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .header td { vertical-align: top; padding: 0 0 15pt; }
        .company, .title { color: #800000; font-size: 16pt; font-weight: bold; }
        .right { text-align: right; }
        .rule { border-top: 1pt solid #800000; }
        .addresses { margin-bottom: 20px; }
        .addresses td { width: 50%; padding-right: 16px; }
        .lines { white-space: pre-line; }
        .items th { background: #800000; color: white; padding: 5pt 3pt; font-size: 10pt; text-align: center; }
        .items td { border: .5pt solid #ccc; padding: 5pt 3pt; }
        .items tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .center { text-align: center; }
        .totals { margin-top: 3px; }
        .totals td { padding: 3px 4px; font-weight: bold; }
        .net { border-top: 1px solid #111; border-bottom: 3px double #111; }
        .terms { margin-top: 16pt; font-size: 10pt; line-height: 1.3; }
        .terms h3 { font-size: 10pt; margin: 0 0 4pt; }
        .signatures { margin-top: 28px; page-break-inside: avoid; }
        .signatures td { width: 50%; padding-right: 35px; }
        .signature { margin-top: 42px; border-top: 1px dashed #333; padding-top: 5px; }
    </style>
</head>
<body>
    @php($document = $document ?? [])
    <table class="header"><tr>
        <td><div class="company">{{ $document['issuer_name'] ?? config('purchase_order.issuer_name') }}</div>
            <div class="lines">{{ $document['issuer_address'] ?? config('purchase_order.issuer_address') }}</div>
            <div>No. Tel: {{ $document['issuer_phone'] ?? config('purchase_order.issuer_phone') }} | E-mel: {{ $document['issuer_email'] ?? config('purchase_order.issuer_email') }}</div>
        </td>
        <td class="right"><div class="title">PESANAN BELIAN</div><strong>No: {{ $order->po_no }}</strong><div>Tarikh: {{ \Carbon\Carbon::parse($order->po_date)->locale('ms')->translatedFormat('d F Y') }}</div></td>
    </tr></table>
    <div class="rule"></div>
    <table class="addresses"><tr>
        <td>Kepada:<br><strong>{{ $order->company_name }}</strong><div class="lines">{{ $order->supplier_address }}</div>U/P: {{ $order->attention_supplier ?: '—' }}<br>No. Tel: {{ $order->supplier_phone ?: '—' }}</td>
        <td>Alamat Penghantaran:<br><strong>{{ $document['delivery_company'] ?? config('purchase_order.issuer_name') }}</strong><div class="lines">{{ $order->delivery_address ?: config('purchase_order.issuer_address') }}</div>U/P: {{ $order->attention_delivery ?: config('purchase_order.delivery_attention') }}<br>No. Tel: {{ $document['delivery_phone'] ?? config('purchase_order.issuer_phone') }}</td>
    </tr></table>
    <table class="items">
        <thead><tr><th width="5%">BIL</th><th width="42%">PERIHAL BARANGAN (ITEM DESCRIPTION)</th><th width="9%">KUANTITI</th><th width="10%">UNIT</th><th width="16%">HARGA SEUNIT<br>(RM)</th><th width="18%">JUMLAH<br>(RM)</th></tr></thead>
        <tbody>@foreach($items as $item)<tr><td class="center">{{ $loop->iteration }}</td><td class="lines">{{ $item->item_description }}</td><td class="center">{{ $item->quantity }}</td><td class="center">{{ $item->unit }}</td><td class="right">{{ number_format($item->unit_price,2) }}</td><td class="right">{{ number_format($item->subtotal,2) }}</td></tr>@endforeach</tbody>
    </table>
    <table class="totals"><tr><td class="right">JUMLAH KASAR:</td><td width="18%" class="right">{{ number_format($order->gross_amount,2) }}</td></tr><tr><td class="right">DISKAUN PUKAL ({{ (float)$order->discount_percent }}%):</td><td class="right">{{ number_format($order->discount_amount,2) }}</td></tr><tr><td class="right">JUMLAH BERSIH:</td><td class="right net">{{ number_format($order->net_amount,2) }}</td></tr></table>
    <div class="terms"><h3>TERMA &amp; SYARAT PEMBELIAN (TERMS &amp; CONDITIONS):</h3><div class="lines">{{ $order->terms_conditions }}</div></div>
        <table class="signatures"><tr><td><strong>Disediakan Oleh:</strong><div class="signature">
            @if($order->prepared_by){{ $order->prepared_by }}<br>@endif
            @if(!empty($document['prepared_role'])){{ $document['prepared_role'] }}@endif
        </div></td><td><strong>Diluluskan Oleh:</strong><div class="signature">
            @if($order->approved_by){{ $order->approved_by }}<br>@endif
            @if(!empty($document['approved_role'])){{ $document['approved_role'] }}@endif
        </div></td></tr></table>
</body>
</html>
