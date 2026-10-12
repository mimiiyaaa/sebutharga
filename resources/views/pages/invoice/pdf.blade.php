<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="utf-8"><title>{{ $invoice->invoice_no }}</title>
<style>
@page { margin: 54pt 42pt; }
body { font-family: Helvetica, Arial, sans-serif; color: #000; font-size: 10pt; line-height: 1.4; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
td, th { vertical-align: top; overflow-wrap: break-word; }
.header td { padding-bottom: 15pt; }
.company { color: #800000; font-size: 16pt; font-weight: bold; line-height: 1.2; }
.title { color: #800000; font-size: 14pt; font-weight: bold; }
.issuer { width: 58%; }
.right { text-align: right; }
.lines { white-space: pre-line; word-wrap: break-word; }
.rule { border-top: 1.5pt solid #800000; }
.bill { margin: 14pt 0 18pt; }
.items th { background: #800000; color: #fff; font-size: 10pt; line-height: 1.3; padding: 5pt 3pt; text-align: center; vertical-align: middle; }
.items td { border: 1pt solid #d0d0d0; padding: 4pt 3pt; vertical-align: bottom; line-height: 1.3; }
.items tr { page-break-inside: avoid; }
thead { display: table-header-group; }
.center { text-align: center; }
.total td { font-weight: bold; padding: 1pt 2pt; }
.total .amount { border-bottom: 2pt solid #800000; width: 18%; }
.payment { margin-top: 16pt; page-break-inside: avoid; }
.signature { margin-top: 30pt; page-break-inside: avoid; }
.signature-line { height: 48pt; width: 115pt; border-bottom: 1pt dotted #000; margin-bottom: 4pt; }
</style>
</head>
<body>
<table class="header"><tr><td class="issuer"><div class="company">{{ $document['issuer_name'] }}</div><div class="lines">{{ $document['issuer_address'] }}</div><div>{{ __('No. Tel:') }} {{ $document['issuer_phone'] ?? '' }} | {{ __('E-mel:') }} {{ $document['issuer_email'] ?? '' }}</div></td><td class="right"><div class="title">{{ __('INVOIS') }}</div><strong>{{ __('No. Dokumen:') }} {{ $invoice->invoice_no }}</strong><div>{{ __('Tarikh:') }} {{ \Carbon\Carbon::parse($invoice->invoice_date)->locale('ms')->translatedFormat('d F Y') }}</div></td></tr></table>
<div class="rule"></div>
<div class="bill"><strong>{{ __('Kepada / Bill To:') }}</strong><div>{{ $document['customer_name'] }}</div><div class="lines">{{ $document['customer_address'] }}</div></div>
<table class="items"><thead><tr><th width="5%">{{ __('BIL') }}</th><th width="49%">{{ __('KETERANGAN / DESCRIPTION') }}</th><th width="11%">{{ __('KUANTITI') }}</th><th width="9%">{{ __('UNIT') }}</th><th width="13%">{{ __('HARGA SEUNIT') }}<br>(RM)</th><th width="13%">{{ __('JUMLAH') }}<br>(RM)</th></tr></thead><tbody>
@foreach($items as $item)<tr><td class="center">{{ $loop->iteration }}</td><td class="lines">{{ $item->item_description }}</td><td class="center">{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td><td class="center">{{ $item->unit }}</td><td class="right">{{ number_format($item->unit_price, 2) }}</td><td class="right">{{ number_format($item->subtotal, 2) }}</td></tr>@endforeach
</tbody></table>
<table class="total"><tr><td class="right">{{ __('JUMLAH BESAR (RM):') }}</td><td class="right amount">{{ number_format($invoice->total_amount, 2) }}</td></tr></table>
<div class="payment"><strong>{{ __('ARAHAN PEMBAYARAN / PAYMENT INSTRUCTIONS:') }}</strong><div class="lines">{{ $document['payment_instructions'] }}</div><div>{{ __('Nama Bank:') }} {{ $document['bank_name'] }}</div><div>{{ __('Nama Akaun:') }} {{ $document['account_name'] }}</div><div>{{ __('No. Akaun:') }} {{ $document['account_no'] }}</div></div>
<div class="signature">{{ __('Disediakan oleh,') }}<div class="signature-line"></div>@if(!empty($document['prepared_by']))<div>{{ $document['prepared_by'] }}</div>@endif<strong>{{ $document['prepared_role'] }}<br>{{ $document['signature_company'] ?? $document['issuer_name'] }}</strong></div>
</body></html>


