<!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<title>{{ $quotation->quotation_no }}</title>
<style>
@page { margin: 25pt; }
* { box-sizing: border-box; }
body { margin: 0; color: #000; background: #fff; font-family: Helvetica, Arial, sans-serif; font-size: 10pt; line-height: 1.3; }
table { width: 100%; border-collapse: collapse; }
.header td { vertical-align: top; padding: 0 0 15pt; }
.company { font-size: 16pt; font-weight: bold; color: #800000; }
.document-title { font-size: 14pt; font-weight: bold; color: #800000; }
.contact { font-size: 9pt; }
.document-meta { width: 32%; text-align: right; }
.rule { border-top: 1pt solid #800000; }
.recipient { margin: 15pt 0; font-size: 10pt; }
.address, .terms-copy { white-space: pre-line; overflow-wrap: break-word; }
.items { table-layout: fixed; }
.items th { background: #800000; color: white; border: .5pt solid #000; padding: 5pt 3pt; font-size: 10pt; text-align: center; }
.items td { border: .5pt solid #ccc; padding: 5pt 3pt; vertical-align: middle; overflow-wrap: break-word; }
.items thead { display: table-header-group; }
.items tr { page-break-inside: avoid; }
.bil { width: 6%; text-align: center; }
.description { width: 46%; }
.quantity { width: 12%; text-align: center; }
.unit { width: 8%; text-align: center; }
.price { width: 14%; text-align: right; }
.amount { width: 14%; text-align: right; }
.total td { padding: 3pt 2pt; font-weight: bold; text-align: right; }
.total-value { width: 14%; border-bottom: 2pt solid #800000; }
.terms { margin-top: 16pt; font-size: 10pt; page-break-inside: avoid; }
.signatures { margin-top: 30pt; page-break-inside: avoid; }
.signatures td { width: 50%; vertical-align: top; }
.signature-right { text-align: right; }
.signature-space { height: 55pt; }
.signature-block { display: inline-block; min-width: 110pt; border-top: 1pt dotted #777; padding-top: 3pt; font-weight: bold; }
.signature-right .signature-block { text-align: center; }
@media screen { body { padding: 33px; } }
</style>
</head>
<body>
<table class="header"><tr>
<td>
<div class="company">{{ config('purchase_order.issuer_name') }}</div>
<div class="contact address">{{ config('purchase_order.issuer_address') }}</div>
<div class="contact">No. Tel: {{ config('purchase_order.issuer_phone') }} | E-mel: {{ config('purchase_order.issuer_email') }}</div>
</td>
<td class="document-meta"><div class="document-title">SEBUTHARGA</div>
<strong>No: {{ $quotation->quotation_no }}</strong>
<div class="contact">Tarikh: {{ \Carbon\Carbon::parse($quotation->quotation_date)->locale('ms')->translatedFormat('d F Y') }}</div>
</td></tr></table>
<div class="rule"></div>
<div class="recipient"><strong>Kepada:</strong><br>
<span>{{ $quotation->customer_name ?: $quotation->company_name }}</span>@if ($quotation->customer_name && $quotation->company_name && $quotation->customer_name !== $quotation->company_name)<br><span>{{ $quotation->company_name }}</span>@endif
<div class="address">{{ $quotation->address ?? '' }}</div>
</div>
<table class="items">
<thead><tr><th class="bil">BIL</th><th class="description">KETERANGAN / DESCRIPTION</th><th class="quantity">KUANTITI</th><th class="unit">UNIT</th><th class="price">HARGA<br>SEUNIT<br>(RM)</th><th class="amount">JUMLAH<br>(RM)</th></tr></thead>
<tbody>
@foreach ($items as $index => $item)
<tr><td class="bil">{{ $index + 1 }}</td><td class="description">{{ $item->item_description }}</td><td class="quantity">{{ $item->quantity }}</td><td class="unit">{{ $item->unit }}</td><td class="price">{{ number_format((float) $item->unit_price, 2) }}</td><td class="amount">{{ number_format((float) $item->subtotal, 2) }}</td></tr>
@endforeach
</tbody></table>
<table class="total"><tr><td>JUMLAH BESAR (RM):</td><td class="total-value">{{ number_format((float) $detail->jumlah_total, 2) }}</td></tr></table>
<div class="terms"><strong>TERMA DAN SYARAT / TERMS &amp; CONDITIONS:</strong>
<div class="terms-copy">{{ $detail->terma_syarat ?: "1. Tempoh sah sebutharga adalah 30 hari dari tarikh sebutharga dikeluarkan.
2. Tempoh penghantaran: 14 - 30 hari selepas penerimaan Pesanan Belian (PO) rasmi.
3. Sila tandatangan di bawah untuk pengesahan persetujuan sebutharga ini." }}</div>
</div>
<table class="signatures"><tr><td>Disediakan oleh,</td><td class="signature-right">Diterima / Disahkan oleh,</td></tr>
<tr><td class="signature-space"></td><td></td></tr>
<tr><td><div class="signature-block">@if ($detail->disediakan_oleh){{ $detail->disediakan_oleh }}<br>@endif @if (property_exists($detail, 'disediakan_role') && $detail->disediakan_role){{ $detail->disediakan_role }}<br>@endif</div></td>
<td class="signature-right"><div class="signature-block">@if ($detail->diterima_oleh){{ $detail->diterima_oleh }}<br>@endif @if (property_exists($detail, 'diterima_role') && $detail->diterima_role){{ $detail->diterima_role }}<br>@endif</div></td></tr></table>
</body></html>
