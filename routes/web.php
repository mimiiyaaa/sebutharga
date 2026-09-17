<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
use App\Http\Controllers\DashboardController;

// dashboard pages
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('pages.dashboard.ecommerce', [
        'title' => 'Dashboard'
    ]);
})->middleware('auth')->name('dashboard');

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->name('login');

/* Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup'); */

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');

// sebut harga pages
Route::get('/purchase-order/create', [\App\Http\Controllers\PurchaseOrderController::class, 'selectQuotation'])
    ->middleware('auth')->name('purchase-order.create');
Route::get('/purchase-order/create/form', [\App\Http\Controllers\PurchaseOrderController::class, 'create'])
    ->middleware('auth')->name('purchase-order.form');
Route::post('/purchase-order', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])
    ->middleware('auth')->name('purchase-order.store');
Route::get('/purchase-order', function () {
    $orders = DB::table('purchase_order_master as po')->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'po.customer_id')->leftJoin('sebutharga_master as quotation', 'quotation.quotation_id', '=', 'po.quotation_id')->orderByDesc('po.purchase_order_id')->get(['po.purchase_order_id','po.po_no','po.quotation_detail_id','quotation.quotation_no as quotation_reference_no','po.po_date','po.net_amount','po.status_po','supplier.company_name']);
    return view('pages.purchase-order.index', ['title' => 'Pesanan Belian (PO)', 'orders' => $orders]);
})->middleware('auth')->name('purchase-order.index');

Route::get('/purchase-order/{id}/pdf', function ($id) {
    $order = DB::table('purchase_order_master as po')->leftJoin('customer_supplier as supplier','supplier.customer_id','=','po.customer_id')->leftJoin('sebutharga_master as quotation','quotation.quotation_id','=','po.quotation_id')->where('po.purchase_order_id',$id)->select('po.*','quotation.quotation_no as quotation_reference_no','supplier.company_name','supplier.address as supplier_address','supplier.phone_no as supplier_phone')->first();
    abort_unless($order,404);
    $items = DB::table('purchase_order_item')->where('purchase_order_id',$id)->orderBy('po_item_id')->get();
    return \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.purchase-order.pdf', compact('order','items'))
        ->setPaper('a4')->setOption('isRemoteEnabled', false)->stream($order->po_no.'.pdf');
})->middleware('auth')->name('purchase-order.pdf');

Route::get('/purchase-order/{id}/edit', [\App\Http\Controllers\PurchaseOrderController::class, 'edit'])->whereNumber('id')->middleware('auth')->name('purchase-order.edit');
Route::put('/purchase-order/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->whereNumber('id')->middleware('auth')->name('purchase-order.update');

Route::get('/purchase-order/{id}', function ($id) {
    $order = DB::table('purchase_order_master as po')->leftJoin('customer_supplier as supplier','supplier.customer_id','=','po.customer_id')->leftJoin('sebutharga_master as quotation','quotation.quotation_id','=','po.quotation_id')->where('po.purchase_order_id',$id)->select('po.*','quotation.quotation_no as quotation_reference_no','supplier.company_name','supplier.address as supplier_address','supplier.phone_no as supplier_phone')->first();
    abort_unless($order,404);
    $items = DB::table('purchase_order_item')->where('purchase_order_id',$id)->orderBy('po_item_id')->get();
    return view('pages.purchase-order.preview', compact('order','items'));
})->middleware('auth')->name('purchase-order.show');
Route::delete('/purchase-order/{id}', function ($id) { DB::table('purchase_order_item')->where('purchase_order_id',$id)->delete(); DB::table('purchase_order_master')->where('purchase_order_id',$id)->delete(); return redirect()->route('purchase-order.index'); })->middleware('auth')->name('purchase-order.delete');

Route::get('/sebut-harga', function () {
    $quotations = DB::table('sebutharga_master as quotation')
        ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'quotation.customer_id')
        ->select([
            'quotation.quotation_id',
            'quotation.quotation_no',
            'quotation.quotation_date',
            'quotation.quotation_title',
            'quotation.status_quotation',
            'quotation.quotation_detail_id',
            'customer.company_name',
            'customer.customer_name',
            'customer.email',
        ])
        ->orderByDesc('quotation.quotation_date')
        ->orderByDesc('quotation.quotation_id')
        ->get();

    $drafts = DB::table('sebutharga_detail')
        ->whereIn('quotation_id', $quotations->pluck('quotation_id'))
        ->orderBy('draft_no')
        ->get()
        ->groupBy('quotation_id');

    $quotations = $quotations->map(function ($quotation) use ($drafts) {
            $name = $quotation->company_name ?: ($quotation->customer_name ?: 'Tiada nama');
            $quotationDrafts = $drafts->get($quotation->quotation_id, collect());
            $selectedDraft = $quotationDrafts->firstWhere('quotation_detail_id', $quotation->quotation_detail_id)
                ?: $quotationDrafts->first();
            $versionStatus = function ($draft) {
                $status = $draft->status_quotation ?? null;
                return $status === null
                    ? (($draft->status_draft ?? null) === 'Final' ? 'Setuju' : 'Menunggu Keputusan')
                    : ((int) $status === 1 ? 'Setuju' : 'Tidak Setuju');
            };
            $initials = collect(preg_split('/\s+/', trim($name)))
                ->filter()
                ->take(2)
                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                ->implode('');

            return [
                'id' => $quotation->quotation_id,
                'quotationNo' => $quotation->quotation_no,
                'customerName' => $name,
                'customerEmail' => $quotation->email ?: '-',
                'initials' => $initials ?: '?',
                'avatarBg' => 'bg-brand-50',
                'avatarColor' => 'text-brand-500',
                'product' => $quotation->quotation_title ?: '-',
                'version' => $selectedDraft?->quotation_detail_id ?: 1,
                'versions' => $quotationDrafts->map(fn ($draft) => [
                    'id' => $draft->quotation_detail_id,
                    'name' => 'Draf ' . $draft->draft_no . ($draft->status_draft === 'Final' ? ' (Final)' : ''),
                    'draftNo' => $draft->draft_no,
                    'title' => $draft->quotation_title ?? $quotation->quotation_title ?? '-',
                    'date' => $draft->quotation_date ?? $quotation->quotation_date,
                    'status' => $draft->status_draft === 'Final' ? 'Final' : 'Draf',
                    'decision' => $versionStatus($draft),
                    'selected' => $quotation->quotation_detail_id === $draft->quotation_detail_id,
                ])->values(),
                // Snapshot columns were added after existing records were created.
                // Use master quotation values as the compatible fallback for old rows.
                'closeDate' => $quotation->quotation_date,
                'status' => ($selectedDraft->status_draft ?? null) === 'Final' ? 'Final' : 'Draf',
                'decision' => $versionStatus($selectedDraft),
            ];
        });

    return view('pages.sebut-harga', [
        'title' => 'Sebut Harga',
        'quotations' => $quotations,
    ]);
})->middleware('auth')->name('sebut-harga');

Route::get('/pelanggan', function () {
    return view('pages.pelanggan', ['title' => 'Pelanggan', 'contacts' => DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')->get()]);
})->middleware('auth')->name('pelanggan');

Route::get('/pembekal', function () {
    return view('pages.pembekal', ['title' => 'Pembekal', 'contacts' => DB::table('customer_supplier')->where('jenis_customer', 2)->orderBy('company_name')->get()]);
})->middleware('auth')->name('pembekal');

Route::get('/{type}/tambah', [\App\Http\Controllers\ContactController::class, 'form'])->whereIn('type', ['pelanggan','pembekal'])->middleware('auth')->name('contacts.create');
Route::post('/{type}/simpan/{id?}', [\App\Http\Controllers\ContactController::class, 'save'])->whereIn('type', ['pelanggan','pembekal'])->middleware('auth')->name('contacts.save');
Route::get('/{type}/{id}', [\App\Http\Controllers\ContactController::class, 'show'])->whereIn('type', ['pelanggan','pembekal'])->middleware('auth')->name('contacts.show');
Route::get('/{type}/{id}/edit', [\App\Http\Controllers\ContactController::class, 'form'])->whereIn('type', ['pelanggan','pembekal'])->middleware('auth')->name('contacts.edit');
Route::delete('/{type}/{id}', [\App\Http\Controllers\ContactController::class, 'delete'])->whereIn('type', ['pelanggan','pembekal'])->middleware('auth')->name('contacts.delete');

// mimi try invoice pages
Route::get('/invoice', function () {
    return view('pages.invoice', ['title' => 'Invoice']);
})->name('invoice');

Route::post('/signin', [AuthController::class, 'login'])
    ->name('signin.store');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

// add sebut harga
Route::get('/sebut-harga/create', function () {
    $customers = DB::table('customer_supplier')
        ->where('jenis_customer', 1)
        ->orderBy('company_name')
        ->get();

    return view('pages.sebut-harga.create', [
        'title' => 'Tambah Sebut Harga',
        'customers' => $customers,
    ]);
})->middleware('auth')->name('sebut-harga.create');

Route::get('/sebut-harga/{id}/pdf', function ($id) {
    $quotation = DB::table('sebutharga_master as master')
        ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'master.customer_id')
        ->where('master.quotation_id', $id)
        ->select('master.*', 'customer.customer_name', 'customer.company_name', 'customer.address', 'customer.phone_no', 'customer.email')
        ->first();
    abort_unless($quotation, 404);
    $detailId = request('draft') ?: $quotation->quotation_detail_id;
    $detail = DB::table('sebutharga_detail')->where('quotation_detail_id', $detailId)->where('quotation_id', $id)->first();
    abort_unless($detail, 404);
    if (property_exists($detail, 'customer_id') && $detail->customer_id) {
        $customer = DB::table('customer_supplier')->where('customer_id', $detail->customer_id)->first();
        if ($customer) {
            foreach (['customer_name', 'company_name', 'address', 'phone_no', 'email'] as $field) {
                $quotation->{$field} = $customer->{$field} ?? $quotation->{$field};
            }
        }
    }
    foreach (['quotation_date', 'quotation_title', 'no_rujukan_pelanggan'] as $field) {
        $quotation->$field = $detail->$field ?? $quotation->$field;
    }
    $items = DB::table('sebutharga_item')->where('quotation_detail_id', $detail->quotation_detail_id)->get();
    return \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.sebut-harga.pdf', compact('quotation', 'detail', 'items'))
        ->setPaper('a4')->setOption('isRemoteEnabled', false)->stream($quotation->quotation_no . '.pdf');
})->middleware('auth')->name('sebut-harga.pdf');

Route::post('/sebut-harga', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'quotation_date' => ['required', 'date'],
        'customer_id' => ['required', 'integer', 'exists:customer_supplier,customer_id'],
        'quotation_title' => ['nullable', 'string', 'max:255'],
        'no_rujukan_pelanggan' => ['nullable', 'string', 'max:100'],
        'status_quotation' => ['nullable', 'in:0,1'],
        'draft_name' => ['nullable', 'string', 'max:100'],
        'terma_syarat' => ['nullable', 'string'],
        'disediakan_oleh' => ['nullable', 'string', 'max:255'],
        'diterima_oleh' => ['nullable', 'string', 'max:255'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.description' => ['required', 'string'],
        'items.*.quantity' => ['required', 'integer', 'min:1'],
        'items.*.unit' => ['required', 'string', 'max:50'],
        'items.*.price' => ['required', 'numeric', 'min:0'],
    ]);

    return DB::transaction(function () use ($validated, $request) {
        $grossAmount = collect($validated['items'])->sum(
            fn ($item) => (int) $item['quantity'] * (float) $item['price']
        );
        $quotationNo = \App\Helpers\QuotationNumber::next($validated['quotation_date']);
        $userId = $request->user()?->id;
        $customerReference = DB::table('customer_supplier')->where('customer_id', $validated['customer_id'])->value('customer_code');

        $quotationId = DB::table('sebutharga_master')->insertGetId([
            'quotation_no' => $quotationNo,
            'customer_id' => $validated['customer_id'],
            'quotation_date' => $validated['quotation_date'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $customerReference,
            'status_quotation' => $validated['status_quotation'] ?? null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detailData = [
            'quotation_id' => $quotationId,
            'draft_no' => 1,
            'draft_name' => null,
            'jumlah_total' => $grossAmount,
            'terma_syarat' => \App\Helpers\QuotationTerms::fromRequest($request),
            'document_data' => \App\Helpers\QuotationDocument::capture($request),
            'disediakan_oleh' => $validated['disediakan_oleh'] ?? null,
            'diterima_oleh' => $validated['diterima_oleh'] ?? null,
            'status_draft' => 'Draf',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        foreach (['quotation_date', 'customer_id', 'quotation_title', 'no_rujukan_pelanggan', 'status_quotation'] as $column) {
            if (Schema::hasColumn('sebutharga_detail', $column)) {
                $detailData[$column] = $column === 'quotation_date' ? $validated['quotation_date'] : ($column === 'customer_id' ? $validated['customer_id'] : ($column === 'no_rujukan_pelanggan' ? $customerReference : ($validated[$column] ?? null)));
            }
        }
        $quotationDetailId = DB::table('sebutharga_detail')->insertGetId($detailData);

        DB::table('sebutharga_master')
            ->where('quotation_id', $quotationId)
            ->update(['quotation_detail_id' => $quotationDetailId]);

        foreach ($validated['items'] as $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['price'];

            DB::table('sebutharga_item')->insert([
                'quotation_detail_id' => $quotationDetailId,
                'item_description' => $item['description'],
                'quantity' => $quantity,
                'unit' => $item['unit'],
                'unit_price' => $unitPrice,
                'subtotal' => round($quantity * $unitPrice, 2),
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('sebut-harga')->with('success', "Sebut harga {$quotationNo} berjaya disimpan sebagai draf.");
    });
})->middleware('auth')->name('sebut-harga.store');

Route::post('/sebut-harga/{id}/draft', function (\Illuminate\Http\Request $request, $id) {
    $validated = $request->validate([
        'quotation_date' => ['required', 'date'],
        'customer_id' => ['required', 'integer', 'exists:customer_supplier,customer_id'],
        'quotation_title' => ['nullable', 'string', 'max:255'],
        'no_rujukan_pelanggan' => ['nullable', 'string', 'max:100'],
        'status_quotation' => ['nullable', 'in:0,1'],
        'update_draft_id' => ['nullable', 'integer'],
        'terma_syarat' => ['nullable', 'string'],
        'disediakan_oleh' => ['nullable', 'string', 'max:255'],
        'diterima_oleh' => ['nullable', 'string', 'max:255'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.description' => ['required', 'string'],
        'items.*.quantity' => ['required', 'integer', 'min:1'],
        'items.*.unit' => ['required', 'string', 'max:50'],
        'items.*.price' => ['required', 'numeric', 'min:0'],
    ]);

    $quotation = DB::table('sebutharga_master')->where('quotation_id', $id)->first();
    abort_unless($quotation, 404);

    DB::transaction(function () use ($validated, $request, $id) {
        DB::table('sebutharga_master')->where('quotation_id', $id)->lockForUpdate()->first();
        // Every revision needs a fresh decision, regardless of the source draft.
        $validated['status_quotation'] = null;
        $total = collect($validated['items'])->sum(fn ($item) => (int) $item['quantity'] * (float) $item['price']);
        $userId = $request->user()?->id;
        $customerReference = DB::table('customer_supplier')->where('customer_id', $validated['customer_id'])->value('customer_code');
        if (! empty($validated['update_draft_id'])) {
            $draftId = (int) $validated['update_draft_id'];
            $draft = DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->where('quotation_id', $id)->lockForUpdate()->first();
            abort_unless($draft && $draft->status_draft !== 'Final', 422, 'Draf ini tidak boleh dikemaskini.');

            DB::table('sebutharga_master')->where('quotation_id', $id)->update([
                'customer_id' => $validated['customer_id'],
                'quotation_title' => $validated['quotation_title'] ?? null,
                'no_rujukan_pelanggan' => $customerReference,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->update([
                'jumlah_total' => $total,
                'terma_syarat' => \App\Helpers\QuotationTerms::fromRequest($request),
                'document_data' => \App\Helpers\QuotationDocument::capture($request),
                'disediakan_oleh' => $validated['disediakan_oleh'] ?? null,
                'diterima_oleh' => $validated['diterima_oleh'] ?? null,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            DB::table('sebutharga_item')->where('quotation_detail_id', $draftId)->delete();
            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $item['price'];
                DB::table('sebutharga_item')->insert([
                    'quotation_detail_id' => $draftId,
                    'item_description' => $item['description'],
                    'quantity' => $quantity,
                    'unit' => $item['unit'],
                    'unit_price' => $unitPrice,
                    'subtotal' => round($quantity * $unitPrice, 2),
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('sebutharga_master')->where('quotation_id', $id)->update(['quotation_detail_id' => $draftId]);
            return;
        }
        $draftNo = ((int) DB::table('sebutharga_detail')->where('quotation_id', $id)->max('draft_no')) + 1;

        DB::table('sebutharga_master')->where('quotation_id', $id)->update([
            'customer_id' => $validated['customer_id'],
            'quotation_date' => $validated['quotation_date'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $customerReference,
            'status_quotation' => $validated['status_quotation'] ?? null,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        $detailData = [
            'quotation_id' => $id,
            'draft_no' => $draftNo,
            'draft_name' => null,
            'jumlah_total' => $total,
            'terma_syarat' => \App\Helpers\QuotationTerms::fromRequest($request),
            'document_data' => \App\Helpers\QuotationDocument::capture($request),
            'disediakan_oleh' => $validated['disediakan_oleh'] ?? null,
            'diterima_oleh' => $validated['diterima_oleh'] ?? null,
            'status_draft' => 'Draf',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        foreach (['quotation_date', 'customer_id', 'quotation_title', 'no_rujukan_pelanggan', 'status_quotation'] as $column) {
            if (Schema::hasColumn('sebutharga_detail', $column)) {
                $detailData[$column] = $column === 'quotation_date' ? $validated['quotation_date'] : ($column === 'customer_id' ? $validated['customer_id'] : ($column === 'no_rujukan_pelanggan' ? $customerReference : ($validated[$column] ?? null)));
            }
        }
        $draftId = DB::table('sebutharga_detail')->insertGetId($detailData);
        DB::table('sebutharga_master')->where('quotation_id', $id)
            ->update(['quotation_detail_id' => $draftId]);

        foreach ($validated['items'] as $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['price'];
            DB::table('sebutharga_item')->insert([
                'quotation_detail_id' => $draftId,
                'item_description' => $item['description'],
                'quantity' => $quantity,
                'unit' => $item['unit'],
                'unit_price' => $unitPrice,
                'subtotal' => round($quantity * $unitPrice, 2),
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });

    return redirect()->route('sebut-harga')->with('success', $request->filled('update_draft_id') ? 'Draf berjaya dikemaskini.' : 'Draft baru berjaya disimpan.');
})->middleware('auth')->name('sebut-harga.draft.store');

Route::get('/sebut-harga/{id}/edit', function ($id) {
    $quotation = DB::table('sebutharga_master as quotation')
        ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'quotation.customer_id')
        ->where('quotation.quotation_id', $id)
        ->select('quotation.*', 'customer.company_name', 'customer.customer_name', 'customer.address')
        ->first();
    abort_unless($quotation, 404);

    $draftId = request('draft') ?: ($quotation->quotation_detail_id ?: DB::table('sebutharga_detail')->where('quotation_id', $id)->value('quotation_detail_id'));
    $draft = DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->where('quotation_id', $id)->first();
    abort_unless($draft, 404);
    $quotation->quotation_date = ($draft->quotation_date ?? null) ?: $quotation->quotation_date;
    $quotation->quotation_title = ($draft->quotation_title ?? null) ?: $quotation->quotation_title;
    $quotation->customer_id = ($draft->customer_id ?? null) ?: $quotation->customer_id;
    $quotation->no_rujukan_pelanggan = ($draft->no_rujukan_pelanggan ?? null) ?: $quotation->no_rujukan_pelanggan;
    $quotation->status_quotation = $draft->status_quotation;
    $draft->items = DB::table('sebutharga_item')->where('quotation_detail_id', $draft->quotation_detail_id)->get();
    $customers = DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')->get();

    return view('pages.sebut-harga.edit', [
        'title' => 'Edit Sebut Harga',
        'quotation' => $quotation,
        'draft' => $draft,
        'customers' => $customers,
    ]);
})->middleware('auth')->name('sebut-harga.edit');

Route::get('/sebut-harga/{id}/preview', function ($id) {
    $quotation = DB::table('sebutharga_master as master')
        ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'master.customer_id')
        ->where('master.quotation_id', $id)
        ->select('master.*', 'customer.customer_name', 'customer.company_name', 'customer.address', 'customer.phone_no', 'customer.email')
        ->first();
    abort_unless($quotation, 404);
    $detailId = request('draft') ?: $quotation->quotation_detail_id;
    $draft = DB::table('sebutharga_detail')->where('quotation_detail_id', $detailId)->where('quotation_id', $id)->first();
    abort_unless($draft, 404);
    foreach (['quotation_date', 'quotation_title', 'no_rujukan_pelanggan'] as $field) {
        $quotation->$field = $draft->$field ?? $quotation->$field;
    }
    $draft->items = DB::table('sebutharga_item')->where('quotation_detail_id', $draft->quotation_detail_id)->get();
    return view('pages.sebut-harga.preview', compact('quotation', 'draft'));
})->middleware('auth')->name('sebut-harga.preview');

Route::patch('/sebut-harga/{id}', function (\Illuminate\Http\Request $request, $id) {
    $validated = $request->validate([
        'quotation_date' => ['required', 'date'],
        'customer_id' => ['required', 'integer', 'exists:customer_supplier,customer_id'],
        'quotation_title' => ['nullable', 'string', 'max:255'],
        'no_rujukan_pelanggan' => ['nullable', 'string', 'max:100'],
        'status_quotation' => ['nullable', 'in:0,1'],
        'terma_syarat' => ['nullable', 'string'],
        'disediakan_oleh' => ['nullable', 'string', 'max:255'],
        'diterima_oleh' => ['nullable', 'string', 'max:255'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.description' => ['required', 'string'],
        'items.*.quantity' => ['required', 'integer', 'min:1'],
        'items.*.unit' => ['required', 'string', 'max:50'],
        'items.*.price' => ['required', 'numeric', 'min:0'],
    ]);

    $quotation = DB::table('sebutharga_master')->where('quotation_id', $id)->first();
    abort_unless($quotation, 404);
    $draftId = $quotation->quotation_detail_id ?: DB::table('sebutharga_detail')->where('quotation_id', $id)->value('quotation_detail_id');
    abort_unless($draftId, 404);
    $draftNo = DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->value('draft_no');

    DB::transaction(function () use ($validated, $request, $id, $draftId, $draftNo) {
        $total = collect($validated['items'])->sum(fn ($item) => (int) $item['quantity'] * (float) $item['price']);
        $userId = $request->user()?->id;
        $customerReference = DB::table('customer_supplier')->where('customer_id', $validated['customer_id'])->value('customer_code');

        DB::table('sebutharga_master')->where('quotation_id', $id)->update([
            'customer_id' => $validated['customer_id'],
            'quotation_date' => $validated['quotation_date'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $customerReference,
            'status_quotation' => $validated['status_quotation'] ?? null,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->update([
            'draft_name' => null,
            'jumlah_total' => $total,
            'terma_syarat' => \App\Helpers\QuotationTerms::fromRequest($request),
            'document_data' => \App\Helpers\QuotationDocument::capture($request),
            'disediakan_oleh' => $validated['disediakan_oleh'] ?? null,
            'diterima_oleh' => $validated['diterima_oleh'] ?? null,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        DB::table('sebutharga_item')->where('quotation_detail_id', $draftId)->delete();
        foreach ($validated['items'] as $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['price'];
            DB::table('sebutharga_item')->insert([
                'quotation_detail_id' => $draftId,
                'item_description' => $item['description'],
                'quantity' => $quantity,
                'unit' => $item['unit'],
                'unit_price' => $unitPrice,
                'subtotal' => round($quantity * $unitPrice, 2),
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });

    return redirect()->route('sebut-harga')->with('success', 'Sebut harga berjaya dikemaskini.');
})->middleware('auth')->name('sebut-harga.update');

Route::delete('/sebut-harga/{id}', function ($id) {
    $quotation = DB::table('sebutharga_master')
        ->where('quotation_id', $id)
        ->first();

    abort_unless($quotation, 404);

    if (DB::table('purchase_order_master')->where('quotation_id', $id)->exists()) {
        return redirect()->route('sebut-harga')->with('error', 'Sebut harga ini tidak boleh dipadam kerana sudah digunakan dalam Purchase Order.');
    }

    DB::transaction(function () use ($id) {
        $detailIds = DB::table('sebutharga_detail')
            ->where('quotation_id', $id)
            ->pluck('quotation_detail_id');

        if ($detailIds->isNotEmpty()) {
            DB::table('sebutharga_item')->whereIn('quotation_detail_id', $detailIds)->delete();
            DB::table('sebutharga_detail')->whereIn('quotation_detail_id', $detailIds)->delete();
        }

        DB::table('sebutharga_master')->where('quotation_id', $id)->delete();
    });

    return redirect()->route('sebut-harga')->with('success', 'Sebut harga berjaya dipadam.');
})->middleware('auth')->name('sebut-harga.destroy');

Route::delete('/sebut-harga/{id}/draft/{draftId}', function ($id, $draftId) {
    return DB::transaction(function () use ($id, $draftId) {
        $quotation = DB::table('sebutharga_master')->where('quotation_id', $id)->lockForUpdate()->first();
        abort_unless($quotation, 404);
        $draft = DB::table('sebutharga_detail')->where('quotation_id', $id)
            ->where('quotation_detail_id', $draftId)->lockForUpdate()->first();
        abort_unless($draft, 404);
        if (DB::table('purchase_order_master')->where('quotation_detail_id', $draftId)->exists()) {
            return redirect()->route('sebut-harga')->with('error', 'Draf ini tidak boleh dipadam kerana sudah digunakan dalam PO.');
        }
        $remaining = DB::table('sebutharga_detail')->where('quotation_id', $id)
            ->where('quotation_detail_id', '<>', $draftId)->orderByDesc('draft_no')->first();
        if ((string) $quotation->quotation_detail_id === (string) $draftId) {
            DB::table('sebutharga_master')->where('quotation_id', $id)->update([
                'quotation_detail_id' => $remaining?->quotation_detail_id,
                'status_quotation' => $remaining?->status_quotation,
                'updated_at' => now(),
            ]);
        }
        DB::table('sebutharga_item')->where('quotation_detail_id', $draftId)->delete();
        DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->delete();
        if (! $remaining && ! DB::table('purchase_order_master')->where('quotation_id', $id)->exists()) {
            DB::table('sebutharga_master')->where('quotation_id', $id)->delete();
        }
        return redirect()->route('sebut-harga')->with('success', 'Draf '.$draft->draft_no.' berjaya dipadam.');
    });
})->middleware('auth')->name('sebut-harga.draft.destroy');

Route::patch('/sebut-harga/{id}/draft/{draftId}', function (\Illuminate\Http\Request $request, $id, $draftId) {
    $request->validate([
        'draft_name' => ['required', 'string', 'max:100'],
    ]);

    $updated = DB::table('sebutharga_detail')
        ->where('quotation_detail_id', $draftId)
        ->where('quotation_id', $id)
        ->update([
            'draft_name' => $request->string('draft_name')->trim()->toString(),
            'updated_at' => now(),
        ]);

    abort_unless($updated, 404);

    return redirect()->route('sebut-harga')->with('success', 'Nama draft berjaya dikemaskini.');
})->middleware('auth')->name('sebut-harga.draft.update');

Route::post('/sebut-harga/{id}/draft/{draftId}/approve', function ($id, $draftId) {
    $draft = DB::table('sebutharga_detail')
        ->where('quotation_detail_id', $draftId)
        ->where('quotation_id', $id)
        ->first();

    abort_unless($draft, 404);

    DB::transaction(function () use ($id, $draftId, $draft) {
        $master = DB::table('sebutharga_master')->where('quotation_id', $id)->lockForUpdate()->first();
        // Finalising a draft confirms it as the active quotation decision.
        // Older drafts may have a null decision; treat the final action as approved.
        $decision = $draft->status_quotation ?? $master?->status_quotation ?? 1;
        DB::table('sebutharga_detail')
            ->where('quotation_id', $id)
            ->update(['status_draft' => 'Draf', 'updated_at' => now()]);

        DB::table('sebutharga_detail')
            ->where('quotation_detail_id', $draftId)
            ->update(['status_draft' => 'Final', 'status_quotation' => $decision, 'updated_at' => now()]);

        DB::table('sebutharga_master')
            ->where('quotation_id', $id)
            ->update([
                'quotation_detail_id' => $draftId,
                'status_quotation' => $decision,
                'updated_at' => now(),
            ]);
    });

    return redirect()->route('sebut-harga')->with('success', 'Draf berjaya dimuktamadkan.');
})->middleware('auth')->name('sebut-harga.draft.approve');

Route::post('/sebut-harga/{id}/draft/{draftId}/reject', function ($id, $draftId) {
    $draft = DB::table('sebutharga_detail')
        ->where('quotation_detail_id', $draftId)
        ->where('quotation_id', $id)
        ->first();

    abort_unless($draft, 404);

    DB::table('sebutharga_master')
        ->where('quotation_id', $id)
        ->update([
            'quotation_detail_id' => $draftId,
            'status_quotation' => 0,
            'updated_at' => now(),
        ]);

    DB::table('sebutharga_detail')
        ->where('quotation_detail_id', $draftId)
        ->update(['status_quotation' => 0, 'updated_at' => now()]);

    return redirect()->route('sebut-harga.edit', [$id, 'draft' => $draftId])->with('success', 'Sebut harga ditandakan sebagai tidak setuju.');
})->middleware('auth')->name('sebut-harga.draft.reject');
