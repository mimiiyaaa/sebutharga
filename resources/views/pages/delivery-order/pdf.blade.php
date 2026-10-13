<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="utf-8"><title>{{ $order->do_no }}</title>
<style>
@page { margin: 54pt 42pt; }
body { font-family: Helvetica, Arial, sans-serif; color: #000; font-size: 10pt; line-height: 1.4; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
td, th { vertical-align: top; overflow-wrap: break-word; }
.header td { padding-bottom: 12pt; }
.issuer-address { margin-top: 4pt; line-height: 1.45; }
.company { color: #800000; font-size: 16pt; font-weight: bold; line-height: 1.2; }
.title { color: #800000; font-size: 13pt; font-weight: bold; }
.issuer { width: 44%; }
.right { text-align: right; }
.lines { white-space: pre-line; word-wrap: break-word; }
.contact { margin-top: 5pt; font-size: 9pt; line-height: 1.45; }
.rule { border-top: 1.5pt solid #800000; }
.recipient { margin: 14pt 0 18pt; }
.items th { background: #800000; color: #fff; font-size: 10pt; padding: 5pt 3pt; text-align: center; }
.items td { border: 1pt solid #d0d0d0; padding: 5pt 3pt; vertical-align: bottom; line-height: 1.3; }
.items tr { page-break-inside: avoid; }
thead { display: table-header-group; }
.center { text-align: center; }
.notes { margin-top: 28pt; font-style: italic; }
.signatures { margin-top: 40pt; page-break-inside: avoid; }
.signatures td { width: 50%; }
.signature-space { height: 52pt; }
.signature-line { border-top: 1pt dotted #000; width: 145pt; padding-top: 5pt; }
.receiver { margin-left: auto; }
.date { margin-top: 12pt; }
</style>
</head>
<body>
@php
    $issuerAddress = trim($document['issuer_address'] ?? '');
    // Keep user-entered address lines; place a postcode on its own line for single-line addresses.
    if (!str_contains($issuerAddress, "\n") && !str_contains($issuerAddress, "\r")) {
        $issuerAddress = preg_replace('/,?\s+(?=\d{5}\b)/u', "\n", $issuerAddress);
    }
@endphp
<table class="header"><tr>
    <td class="issuer">
        <div class="company">{{ $document['issuer_name'] }}</div>
        <div class="issuer-address lines">{{ $issuerAddress }}</div>
        <div class="contact">
            @if(!empty($document['issuer_phone']))<div>{{ __('No. Tel:') }} {{ $document['issuer_phone'] }}</div>@endif
            @if(!empty($document['issuer_email']))<div>{{ __('E-mel:') }} {{ $document['issuer_email'] }}</div>@endif
        </div>
    </td>
    <td class="right"><div class="title">{{ __('NOTA SERAHAN / DELIVERY ORDER') }}</div><strong>{{ __('No. Dokumen:') }} {{ $order->do_no }}</strong><div>{{ __('Tarikh:') }} {{ \Carbon\Carbon::parse($order->do_date)->locale('ms')->translatedFormat('d F Y') }}</div></td>
</tr></table>
<div class="rule"></div>
<div class="recipient"><strong>{{ __('Dihantar Kepada') }}</strong><div>{{ $document['customer_name'] }}</div><div class="lines">{{ $document['customer_address'] }}</div></div>
<table class="items"><thead><tr><th width="5%">{{ __('BIL') }}</th><th width="52%">{{ __('KETERANGAN BARANG') }}</th><th width="12%">{{ __('KUANTITI') }}</th><th width="9%">{{ __('UNIT') }}</th><th width="22%">{{ __('SEMAKAN (/)') }}</th></tr></thead><tbody>
@foreach($items as $item)<tr><td class="center">{{ $loop->iteration }}</td><td class="lines">{{ $item->item_description }}</td><td class="center">{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td><td class="center">{{ $item->unit }}</td><td class="center">{{ $item->remarks === '/' ? '/' : '' }}</td></tr>@endforeach
</tbody></table>
<div class="notes lines">{{ $document['notes'] }}</div>
<table class="signatures"><tr><td>{{ __('Dihantar oleh,') }}</td><td class="right">{{ __('Diterima dalam keadaan baik oleh,') }}</td></tr><tr><td><div class="signature-space"></div><div class="signature-line">@if(!empty($document['delivered_by']))<div>{{ $document['delivered_by'] }}</div>@endif<strong>{{ $document['signature_company'] ?? $document['issuer_name'] }}</strong></div></td><td class="right"><div class="signature-space"></div><div class="signature-line receiver">@if(!empty($document['recipient_name']))<div>{{ $document['recipient_name'] }}</div>@endif<strong>{{ __('Nama & Cop Pelanggan') }}</strong><div class="date">{{ __('Tarikh:') }} {{ !empty($document['received_date']) ? \Carbon\Carbon::parse($document['received_date'])->format('d/m/Y') : '........................' }}</div></div></td></tr></table>
</body></html>
