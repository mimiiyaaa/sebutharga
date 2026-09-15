<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;

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
    $orders = DB::table('purchase_order_master as po')->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'po.customer_id')->orderByDesc('po.purchase_order_id')->get(['po.purchase_order_id','po.po_no','po.quotation_detail_id','po.po_date','po.net_amount','po.status_po','supplier.company_name']);
    return view('pages.purchase-order.index', ['title' => 'Pesanan Belian (PO)', 'orders' => $orders]);
})->middleware('auth')->name('purchase-order.index');

Route::get('/purchase-order/{id}/pdf', function ($id) {
    $order = DB::table('purchase_order_master as po')->leftJoin('customer_supplier as supplier','supplier.customer_id','=','po.customer_id')->where('po.purchase_order_id',$id)->select('po.*','supplier.company_name','supplier.address as supplier_address','supplier.phone_no as supplier_phone')->first();
    abort_unless($order,404);
    $items = DB::table('purchase_order_item')->where('purchase_order_id',$id)->orderBy('po_item_id')->get();
    return \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.purchase-order.pdf', compact('order','items'))
        ->setPaper('a4')->setOption('isRemoteEnabled', false)->stream($order->po_no.'.pdf');
})->middleware('auth')->name('purchase-order.pdf');

Route::get('/purchase-order/{id}/edit', [\App\Http\Controllers\PurchaseOrderController::class, 'edit'])->whereNumber('id')->middleware('auth')->name('purchase-order.edit');
Route::put('/purchase-order/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->whereNumber('id')->middleware('auth')->name('purchase-order.update');

Route::get('/purchase-order/{id}', function ($id) {
    $order = DB::table('purchase_order_master as po')->leftJoin('customer_supplier as supplier','supplier.customer_id','=','po.customer_id')->where('po.purchase_order_id',$id)->select('po.*','supplier.company_name','supplier.address as supplier_address','supplier.phone_no as supplier_phone')->first();
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
            $versionStatus = fn ($status) => $status === 1
                ? 'Setuju'
                : ($status === 0 ? 'Tidak Setuju' : 'Menunggu Keputusan');
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
                'product' => $selectedDraft?->quotation_title ?: ($quotation->quotation_title ?: '-'),
                'version' => $selectedDraft?->quotation_detail_id ?: 1,
                'versions' => $quotationDrafts->map(fn ($draft) => [
                    'id' => $draft->quotation_detail_id,
                    'name' => 'Versi ' . $draft->draft_no,
                    'draftNo' => $draft->draft_no,
                    'title' => $draft->quotation_title ?: ($quotation->quotation_title ?: '-'),
                    'date' => $draft->quotation_date ?: $quotation->quotation_date,
                    'status' => $versionStatus($draft->status_quotation ?? $quotation->status_quotation),
                    'selected' => $quotation->quotation_detail_id === $draft->quotation_detail_id,
                ])->values(),
                'closeDate' => $selectedDraft?->quotation_date ?: $quotation->quotation_date,
                'status' => $versionStatus($selectedDraft?->status_quotation),
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

Route::post('/sebut-harga', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'quotation_date' => ['required', 'date'],
        'customer_id' => ['required', 'integer', 'exists:customer_supplier,customer_id'],
        'quotation_title' => ['nullable', 'string', 'max:255'],
        'no_rujukan_pelanggan' => ['nullable', 'string', 'max:100'],
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
        $quotationYear = date('Y', strtotime($validated['quotation_date']));
        $quotationPrefix = 'K1R-QT-' . $quotationYear . '-';
        $quotationSequence = DB::table('sebutharga_master')
            ->where('quotation_no', 'like', $quotationPrefix . '%')
            ->count() + 1;
        $quotationNo = $quotationPrefix . str_pad((string) $quotationSequence, 3, '0', STR_PAD_LEFT);
        $userId = $request->user()?->id;

        $quotationId = DB::table('sebutharga_master')->insertGetId([
            'quotation_no' => $quotationNo,
            'customer_id' => $validated['customer_id'],
            'quotation_date' => $validated['quotation_date'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $validated['no_rujukan_pelanggan'] ?? null,
            'status_quotation' => null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $quotationDetailId = DB::table('sebutharga_detail')->insertGetId([
            'quotation_id' => $quotationId,
            'draft_no' => 1,
            'quotation_date' => $validated['quotation_date'],
            'customer_id' => $validated['customer_id'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $validated['no_rujukan_pelanggan'] ?? null,
            'status_quotation' => null,
            'draft_name' => null,
            'jumlah_total' => $grossAmount,
            'terma_syarat' => $validated['terma_syarat'] ?? null,
            'disediakan_oleh' => $validated['disediakan_oleh'] ?? null,
            'diterima_oleh' => $validated['diterima_oleh'] ?? null,
            'status_draft' => 'Draf',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
        $total = collect($validated['items'])->sum(fn ($item) => (int) $item['quantity'] * (float) $item['price']);
        $userId = $request->user()?->id;
        $draftNo = ((int) DB::table('sebutharga_detail')->where('quotation_id', $id)->max('draft_no')) + 1;

        DB::table('sebutharga_master')->where('quotation_id', $id)->update([
            'customer_id' => $validated['customer_id'],
            'quotation_date' => $validated['quotation_date'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $validated['no_rujukan_pelanggan'] ?? null,
            'status_quotation' => null,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        $draftId = DB::table('sebutharga_detail')->insertGetId([
            'quotation_id' => $id,
            'draft_no' => $draftNo,
            'quotation_date' => $validated['quotation_date'],
            'customer_id' => $validated['customer_id'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $validated['no_rujukan_pelanggan'] ?? null,
            'status_quotation' => null,
            'draft_name' => null,
            'jumlah_total' => $total,
            'terma_syarat' => $validated['terma_syarat'] ?? null,
            'disediakan_oleh' => $validated['disediakan_oleh'] ?? null,
            'diterima_oleh' => $validated['diterima_oleh'] ?? null,
            'status_draft' => 'Draf',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

    return redirect()->route('sebut-harga')->with('success', 'Draft baru berjaya disimpan.');
})->middleware('auth')->name('sebut-harga.draft.store');

Route::get('/sebut-harga/{id}/edit', function ($id) {
    $quotation = DB::table('sebutharga_master as quotation')
        ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'quotation.customer_id')
        ->where('quotation.quotation_id', $id)
        ->select('quotation.*', 'customer.company_name', 'customer.customer_name')
        ->first();
    abort_unless($quotation, 404);

    $draftId = request('draft') ?: ($quotation->quotation_detail_id ?: DB::table('sebutharga_detail')->where('quotation_id', $id)->value('quotation_detail_id'));
    $draft = DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->where('quotation_id', $id)->first();
    abort_unless($draft, 404);
    $quotation->quotation_date = $draft->quotation_date ?: $quotation->quotation_date;
    $quotation->quotation_title = $draft->quotation_title ?: $quotation->quotation_title;
    $quotation->customer_id = $draft->customer_id ?: $quotation->customer_id;
    $quotation->no_rujukan_pelanggan = $draft->no_rujukan_pelanggan ?: $quotation->no_rujukan_pelanggan;
    $draft->items = DB::table('sebutharga_item')->where('quotation_detail_id', $draft->quotation_detail_id)->get();
    $customers = DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')->get();

    return view('pages.sebut-harga.edit', [
        'title' => 'Edit Sebut Harga',
        'quotation' => $quotation,
        'draft' => $draft,
        'customers' => $customers,
    ]);
})->middleware('auth')->name('sebut-harga.edit');

Route::patch('/sebut-harga/{id}', function (\Illuminate\Http\Request $request, $id) {
    $validated = $request->validate([
        'quotation_date' => ['required', 'date'],
        'customer_id' => ['required', 'integer', 'exists:customer_supplier,customer_id'],
        'quotation_title' => ['nullable', 'string', 'max:255'],
        'no_rujukan_pelanggan' => ['nullable', 'string', 'max:100'],
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

        DB::table('sebutharga_master')->where('quotation_id', $id)->update([
            'customer_id' => $validated['customer_id'],
            'quotation_date' => $validated['quotation_date'],
            'quotation_title' => $validated['quotation_title'] ?? null,
            'no_rujukan_pelanggan' => $validated['no_rujukan_pelanggan'] ?? null,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->update([
            'draft_name' => null,
            'jumlah_total' => $total,
            'terma_syarat' => $validated['terma_syarat'] ?? null,
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

    DB::transaction(function () use ($id, $draftId) {
        DB::table('sebutharga_detail')
            ->where('quotation_id', $id)
            ->update(['status_draft' => 'Draf', 'updated_at' => now()]);

        DB::table('sebutharga_detail')
            ->where('quotation_detail_id', $draftId)
            ->update(['status_draft' => 'Final', 'updated_at' => now()]);

        DB::table('sebutharga_master')
            ->where('quotation_id', $id)
            ->update([
                'quotation_detail_id' => $draftId,
                'status_quotation' => 1,
                'updated_at' => now(),
            ]);
    });

    return redirect()->route('sebut-harga')->with('success', 'Draft berjaya dipersetujui.');
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
