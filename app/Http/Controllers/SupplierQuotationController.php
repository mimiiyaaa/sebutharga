<?php

namespace App\Http\Controllers;

use App\Services\SupplierQuotationOcr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SupplierQuotationController extends Controller
{
    public function index()
    {
        $quotations = DB::table('pembekal_quotation_master as quotation')
            ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'quotation.supplier_id')
            ->orderByDesc('quotation.supplier_quotation_id')
            ->get(['quotation.*', 'supplier.company_name as supplier_registered_name']);

        return view('pages.sebut-harga-pembekal.index', compact('quotations'));
    }

    public function form(?int $id = null)
    {
        $quotation = $id ? DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->firstOrFail() : null;
        $items = $quotation ? DB::table('pembekal_quotation_item')->where('supplier_quotation_id', $id)->get() : collect();
        $suppliers = DB::table('customer_supplier')->where('jenis_customer', 2)->orderBy('company_name')->get(['customer_id', 'company_name', 'customer_name']);

        return view('pages.sebut-harga-pembekal.form', compact('quotation', 'items', 'suppliers'));
    }

    public function extract(Request $request)
    {
        $data = $request->validate([
            'supplier_quotation_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        try {
            $extracted = app(SupplierQuotationOcr::class)->extractText($data['supplier_quotation_file']);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $parsed = $this->parsePdfText($extracted['text']);
        $supplier = $parsed['supplier_name']
            ? DB::table('customer_supplier')
                ->where('jenis_customer', 2)
                ->whereRaw('UPPER(TRIM(company_name)) = ?', [strtoupper(trim($parsed['supplier_name']))])
                ->first(['customer_id'])
            : null;

        return response()->json([
            'data' => [
                'supplier_id' => $supplier?->customer_id,
                'quotation_no_supplier' => $parsed['quotation_no'],
                'quotation_title' => $parsed['quotation_title'],
                'person_in_charge' => $parsed['person_in_charge'],
                'items' => $parsed['items'],
            ],
            'supplier_name' => $parsed['supplier_name'],
            'message' => count($parsed['items']) . ' item berjaya dibaca melalui ' . $extracted['method'] . '. Sila semak sebelum simpan.',
        ]);
    }

    public function registerSupplier(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone_no' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $existing = DB::table('customer_supplier')
            ->where('jenis_customer', 2)
            ->whereRaw('UPPER(TRIM(company_name)) = ?', [strtoupper(trim($data['company_name']))])
            ->first(['customer_id', 'company_name']);

        if ($existing) {
            return response()->json([
                'supplier' => ['customer_id' => $existing->customer_id, 'company_name' => $existing->company_name],
                'message' => 'Pembekal ini sudah berdaftar dan telah dipilih.',
            ]);
        }

        $usedCodes = DB::table('customer_supplier')
            ->where('jenis_customer', 2)
            ->pluck('customer_code')
            ->map(fn ($code) => (int) preg_replace('/\D+/', '', $code))
            ->filter(fn ($number) => $number >= 201 && $number <= 299)
            ->all();
        $nextNumber = collect(range(201, 299))->first(fn ($number) => ! in_array($number, $usedCodes, true));

        if (! $nextNumber) {
            return response()->json(['message' => 'Had maksimum pembekal telah dicapai.'], 422);
        }

        $supplierId = DB::table('customer_supplier')->insertGetId([
            'customer_code' => 'S' . $nextNumber,
            'jenis_customer' => 2,
            'customer_name' => $data['customer_name'] ?? null,
            'company_name' => trim($data['company_name']),
            'address' => $data['address'],
            'phone_no' => $data['phone_no'] ?? null,
            'email' => $data['email'] ?? null,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'supplier' => ['customer_id' => $supplierId, 'company_name' => trim($data['company_name'])],
            'message' => 'Pembekal berjaya didaftarkan dan telah dipilih.',
        ]);
    }

    private function parsePdfText(string $text): array
    {
        $lines = collect(preg_split('/\R/u', str_replace("\r", '', $text)))
            ->map(fn ($line) => trim(preg_replace('/\s+/u', ' ', $line)))
            ->filter()
            ->values();
        $joined = $lines->implode("\n");

        preg_match('/\bNo\.?\s*:\s*([A-Z0-9][A-Z0-9\-\/]+)/i', $joined, $quotationMatch);
        preg_match('/\bTarikh\s*:\s*([^\n]+)/i', $joined, $dateMatch);

        $tableIndex = $lines->search(fn ($line) => preg_match('/^BIL\b/i', $line));
        $firstLine = $lines->first() ?: '';
        preg_match('/^(.+?)\s+SEBUTHARGA\b/i', $firstLine, $issuerMatch);
        $supplierName = trim($issuerMatch[1] ?? '');

        $toIndex = $lines->search(fn ($line) => strtolower($line) === 'kepada:');
        $personInCharge = null;
        $quotationTitle = null;
        if ($toIndex !== false) {
            $recipientEnd = $tableIndex === false ? 6 : max(0, $tableIndex - $toIndex - 1);
            $recipientLines = $lines->slice($toIndex + 1, $recipientEnd)->values();
            $personInCharge = $recipientLines->first();
            $titleCandidates = $recipientLines->skip(2)->filter(fn ($line) => ! preg_match('/^(No\.?\s*Tel|E-mel|\d|[A-Za-z]+,)/i', $line));
            $quotationTitle = $titleCandidates->last() ?: null;
        }

        $items = [];
        if ($tableIndex !== false) {
            $pendingDescription = null;
            $lastItemNumber = null;
            $deferredPricing = null;
            foreach ($lines->slice($tableIndex + 1) as $line) {
                if (preg_match('/^JUMLAH BESAR/i', $line)) {
                    break;
                }
                if (preg_match('/^(\d+)\s+(Sesi)\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})$/i', $line, $match)) {
                    // Some PDF table layouts emit the next row's price columns
                    // before its description. Hold this row until that description appears.
                    $deferredPricing = [
                        'quantity' => (float) $match[1],
                        'unit' => $match[2],
                        'price' => (float) str_replace(',', '', $match[3]),
                    ];
                } elseif (preg_match('/^(\d+)\s+([A-Za-z]+)\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})$/', $line, $match) && ! empty($items)) {
                    $previous = end($items);
                    $items[] = [
                        'item_number' => $previous['item_number'] ?? count($items),
                        'description' => $previous['description'],
                        'quantity' => (float) $match[1],
                        'unit' => $match[2],
                        'price' => (float) str_replace(',', '', $match[3]),
                    ];
                    $lastItemNumber = (int) $match[1];
                } elseif (preg_match('/^(?:(\d+)\s+)?(.+?)\s+(\d+(?:\.\d+)?)\s+([A-Za-z]+)\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})$/', $line, $match)) {
                    $rowNumber = $match[1] !== '' ? (int) $match[1] : (count($items) + 1);
                    $description = trim($match[2]);
                    if (str_starts_with($description, '(') && $pendingDescription) {
                        $description = trim($pendingDescription . ' ' . $description);
                    }
                    $previous = end($items);
                    if ($match[1] !== '' && $previous && ($previous['item_number'] ?? null) === $rowNumber) {
                        $description = $previous['description'];
                    }
                    $items[] = [
                        'item_number' => $rowNumber,
                        'description' => $description,
                        'quantity' => (float) $match[3],
                        'unit' => $match[4],
                        'price' => (float) str_replace(',', '', $match[5]),
                    ];
                    $pendingDescription = $description;
                    $lastItemNumber = $rowNumber;
                } elseif (preg_match('/^(\d+)\s+\(/', $line, $match) && $lastItemNumber !== (int) $match[1]) {
                    $items[] = [
                        'item_number' => (int) $match[1],
                        'description' => $pendingDescription ?: 'Item ' . $match[1],
                        'quantity' => $deferredPricing['quantity'] ?? 1,
                        'unit' => $deferredPricing['unit'] ?? 'unit',
                        'price' => $deferredPricing['price'] ?? 0,
                    ];
                    $lastItemNumber = (int) $match[1];
                    $deferredPricing = null;
                } elseif (preg_match('/^(\d+)\s+(.+)$/', $line, $match) && ! str_starts_with(trim($match[2]), '(')) {
                    $items[] = [
                        'item_number' => (int) $match[1],
                        'description' => trim($match[2]),
                        'quantity' => 1,
                        'unit' => 'unit',
                        'price' => 0,
                    ];
                    $pendingDescription = trim($match[2]);
                    $lastItemNumber = (int) $match[1];
                } elseif (preg_match('/^(\d+)\s+(\(.+)$/', $line, $match) && $items && $lastItemNumber === (int) $match[1]) {
                    $lastIndex = array_key_last($items);
                    $items[$lastIndex]['description'] = trim($items[$lastIndex]['description'] . ' ' . $match[2]);
                    $pendingDescription = $items[$lastIndex]['description'];
                } elseif ($items && (str_starts_with($line, '(') || str_ends_with($line, ')')) && ! preg_match('/^[\d,\.\s]+$/', $line)) {
                    $lastIndex = array_key_last($items);
                    $continuation = preg_replace('/\s+[\d,]+\.\d{2}$/', '', $line);
                    $description = trim($items[$lastIndex]['description'] . ' ' . $continuation);
                    $itemNumber = $items[$lastIndex]['item_number'] ?? null;
                    foreach ($items as $index => $item) {
                        if ($itemNumber === null || ($item['item_number'] ?? null) === $itemNumber) {
                            $items[$index]['description'] = $description;
                        }
                    }
                    $pendingDescription = $description;
                } elseif (preg_match('/[A-Za-z]/', $line) && ! preg_match('/^(SEUNIT|TERMA|Diterima|Disediakan)/i', $line)) {
                    $pendingDescription = trim($line);
                }
            }
        }

        $items = collect($items)->map(fn ($item) => collect($item)->except('item_number')->all())->values()->all();

        return [
            'quotation_no' => $quotationMatch[1] ?? null,
            'quotation_date' => $dateMatch[1] ?? null,
            'quotation_title' => $quotationTitle,
            'person_in_charge' => $personInCharge,
            'supplier_name' => $supplierName,
            'items' => $items,
        ];
    }

    public function save(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'quotation_id' => ['nullable', 'integer', 'exists:sebutharga_master,quotation_id'],
            'supplier_id' => ['required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 2)],
            'quotation_no_supplier' => ['required', 'string', 'max:100'],
            'quotation_title' => ['nullable', 'string', 'max:255'],
            'person_in_charge' => ['nullable', 'string', 'max:255'],
            'supplier_quotation_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $supplier = DB::table('customer_supplier')->where('customer_id', $data['supplier_id'])->firstOrFail();
        $items = collect($data['items']);
        $filePath = null;
        $fileName = null;

        $file = $request->file('supplier_quotation_file');
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $temporaryPath = $file->getPathname();
            if (! $file->isValid() || ! $temporaryPath || ! is_file($temporaryPath)) {
                return back()->withErrors(['supplier_quotation_file' => 'Fail dokumen tidak berjaya diterima. Sila pilih fail semula.'])->withInput();
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $storedName = Str::uuid() . '.' . $extension;
            $filePath = 'pembekal-quotations/' . $storedName;
            Storage::disk('public')->put($filePath, file_get_contents($temporaryPath));
            $fileName = $file->getClientOriginalName();
        }

        DB::transaction(function () use ($data, $items, $supplier, $id, $filePath, $fileName) {
            $record = [
                'quotation_id' => $data['quotation_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'quotation_no_supplier' => $data['quotation_no_supplier'],
                'company_name' => $supplier->company_name,
                'quotation_title' => $data['quotation_title'] ?? null,
                'person_in_charge' => $data['person_in_charge'] ?? null,
                'updated_at' => now(),
            ];
            if ($filePath) {
                $record['file_path'] = $filePath;
                $record['file_name'] = $fileName;
            }

            if ($id) {
                DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->update($record);
                DB::table('pembekal_quotation_item')->where('supplier_quotation_id', $id)->delete();
                $supplierQuotationId = $id;
            } else {
                $record['created_at'] = now();
                $supplierQuotationId = DB::table('pembekal_quotation_master')->insertGetId($record);
            }

            foreach ($items as $item) {
                $quantity = (float) $item['quantity'];
                $price = (float) $item['price'];
                DB::table('pembekal_quotation_item')->insert([
                    'supplier_quotation_id' => $supplierQuotationId,
                    'item_description' => $item['description'],
                    'quantity' => $quantity,
                    'unit' => $item['unit'],
                    'unit_price' => $price,
                    'subtotal' => round($quantity * $price, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('sebut-harga-pembekal')->with('success', 'Sebut harga pembekal berjaya disimpan.');
    }

    public function delete(int $id)
    {
        $quotation = DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->firstOrFail();
        if ($quotation->file_path) Storage::disk('public')->delete($quotation->file_path);
        DB::table('pembekal_quotation_item')->where('supplier_quotation_id', $id)->delete();
        DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->delete();

        return redirect()->route('sebut-harga-pembekal');
    }

    public function download(int $id)
    {
        $quotation = DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->firstOrFail();
        abort_unless($quotation->file_path && Storage::disk('public')->exists($quotation->file_path), 404);

        return Storage::disk('public')->download($quotation->file_path, $quotation->file_name ?: 'sebut-harga-pembekal.pdf');
    }
}
