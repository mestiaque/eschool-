<?php

namespace ME\SchoolManagement\Http\Controllers;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;

use Illuminate\Http\Request;
use ME\SchoolManagement\Models\ActivityLog;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Product;
use ME\SchoolManagement\Models\Stock;
use ME\SchoolManagement\Models\Purchase;
use ME\SchoolManagement\Models\Supplier;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use GuardsDeletes;

    // Users, Roles and Permissions are managed by the metheme host theme
    // (ME\Http\Controllers\UserController / RoleController, routes named
    // me.users.* / me.roles.*) and are intentionally not duplicated here.

    // Activity Logs
    // The theme's Activity Log records every request and data change.
    public function activityLogs()
    {
        return redirect()->route('activity.index');
    }

    // Login, logout and profile management are handled by the metheme host
    // theme (ME\Http\Controllers\Auth\AuthController / ProfileController).

    // Teacher login linking
    public function teacherLinkUser(Request $request, $teacherId)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        Teacher::findOrFail($teacherId);

        \ME\Models\User::where('teacher_id', $teacherId)->update(['teacher_id' => null]);
        \ME\Models\User::where('id', $request->user_id)->update(['teacher_id' => $teacherId]);

        return back()->with('success', 'Login account linked to this teacher');
    }

    public function teacherUnlinkUser($teacherId)
    {
        \ME\Models\User::where('teacher_id', $teacherId)->update(['teacher_id' => null]);
        return back()->with('success', 'Login account unlinked from this teacher');
    }

    // Settings
    public function schoolSettings()
    {
        return view('school-management::settings.school', ['settings' => SchoolSetting::allValues()]);
    }

    public function updateSchoolSettings(Request $request)
    {
        $data = $request->validate([
            'school_name'    => 'required|string|max:255',
            'school_address' => 'nullable|string|max:1000',
            'school_phone'   => 'nullable|string|max:50',
            'school_email'   => 'nullable|email|max:255',
            'school_eiin'    => 'nullable|string|max:50',
            'school_code'    => 'nullable|string|max:50',
        ]);
        $this->saveSettings($data);
        return back()->with('success', 'School settings updated');
    }

    public function smsSettings()
    {
        return view('school-management::settings.sms', [
            'settings' => SchoolSetting::allValues(),
            'events'   => \ME\SchoolManagement\Services\SchoolNotifier::EVENTS,
        ]);
    }

    public function updateSmsSettings(Request $request)
    {
        $values = [];
        foreach (array_keys(\ME\SchoolManagement\Services\SchoolNotifier::EVENTS) as $key) {
            $values[$key] = $request->boolean($key) ? '1' : '0';
        }
        $this->saveSettings($values);
        return back()->with('success', 'Notification settings saved');
    }

    public function backupSettings()
    {
        return view('school-management::settings.backup');
    }

    public function createBackup(Request $request)
    {
        $file = storage_path('app/backup-' . date('Y-m-d-His') . '.sql');
        if ($error = $this->dumpDatabase($file)) {
            return back()->with('error', $error);
        }
        return response()->download($file)->deleteFileAfterSend();
    }

    /**
     * Replace the database with an uploaded .sql backup. Super admin only; a
     * safety backup is written to storage/app/backups first.
     */
    public function restoreBackup(Request $request)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Only a super admin can restore a backup.');
        $request->validate([
            'backup_file' => 'required|file|max:512000',
            'confirm'     => 'required|in:RESTORE',
        ], ['confirm.in' => 'Type RESTORE (capital letters) to confirm.']);

        $upload = $request->file('backup_file');
        if (strtolower($upload->getClientOriginalExtension()) !== 'sql') {
            return back()->with('error', 'Choose a .sql file created by "Download Backup Now".');
        }

        $safety = storage_path('app/backups/pre-restore-' . date('Y-m-d-His') . '.sql');
        @mkdir(dirname($safety), 0775, true);
        if ($error = $this->dumpDatabase($safety)) {
            return back()->with('error', 'Restore cancelled — could not take a safety backup first: ' . $error);
        }

        $db = $this->mysqlConfig();
        $result = \Illuminate\Support\Facades\Process::env(['MYSQL_PWD' => (string) $db['password']])
            ->input(fopen($upload->getRealPath(), 'r'))
            ->timeout(600)
            ->run(['mysql', '--host=' . $db['host'], '--port=' . $db['port'], '--user=' . $db['username'], $db['database']]);

        if (!$result->successful()) {
            return back()->with('error', 'Restore failed: ' . \Illuminate\Support\Str::limit(trim($result->errorOutput()), 300)
                . ' A safety backup is at ' . basename($safety) . '.');
        }

        \Illuminate\Support\Facades\Cache::flush();
        return back()->with('success', 'Backup restored. The previous data was saved as storage/app/backups/' . basename($safety) . '.');
    }

    private function mysqlConfig(): array
    {
        return config('database.connections.' . config('database.default'));
    }

    /** Write a mysqldump of the database to $file; returns an error message or null. */
    private function dumpDatabase(string $file): ?string
    {
        $db = $this->mysqlConfig();
        if (!in_array($db['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            return 'Backup is only supported for MySQL/MariaDB databases.';
        }

        // Password via env so it does not appear in the process list.
        $result = \Illuminate\Support\Facades\Process::env(['MYSQL_PWD' => (string) $db['password']])->timeout(600)
            ->run(['mysqldump', '--single-transaction', '--routines', '--host=' . $db['host'], '--port=' . $db['port'],
                '--user=' . $db['username'], '--result-file=' . $file, $db['database']]);

        if (!$result->successful() || !is_file($file) || filesize($file) === 0) {
            @unlink($file);
            return 'mysqldump is not available or could not connect.';
        }
        return null;
    }

    public function sessionSettings()
    {
        return view('school-management::settings.session', [
            'settings'      => SchoolSetting::allValues(),
            'academicYears' => AcademicYear::orderBy('start_date', 'desc')->get(),
        ]);
    }

    public function updateSessionSettings(Request $request)
    {
        $data = $request->validate([
            'current_year_id'     => 'required|exists:academic_years,id',
            'session_start_month' => 'required|integer|min:1|max:12',
        ]);
        DB::transaction(function () use ($data, $request) {
            AcademicYear::where('id', '!=', $data['current_year_id'])->update(['is_current' => false]);
            AcademicYear::where('id', $data['current_year_id'])->update(['is_current' => true]);
            $this->saveSettings(['session_start_month' => $data['session_start_month']]);
        });
        return back()->with('success', 'Session settings updated');
    }

    public function generalSettings()
    {
        return view('school-management::settings.general', [
            'settings'          => SchoolSetting::allValues(),
            'expenseCategories' => \ME\SchoolManagement\Models\ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function updateGeneralSettings(Request $request)
    {
        $data = $request->validate([
            'currency'    => 'required|in:BDT,USD,INR',
            'timezone'    => 'required|timezone',
            'date_format' => 'required|in:d M Y,Y-m-d,d-m-Y,m-d-Y,d/m/Y',
            'salary_expense_category_id' => 'nullable|exists:expense_categories,id',
        ]);
        $data['salary_expense_category_id'] = $data['salary_expense_category_id'] ?? '';
        $data['optional_subject_bonus']     = $request->boolean('optional_subject_bonus') ? '1' : '0';
        $request->validate(['weekend_days' => 'nullable|array|max:6', 'weekend_days.*' => 'integer|between:0,6']);
        $data['weekend_days'] = collect($request->input('weekend_days', []))->map(fn ($d) => (int) $d)->unique()->sort()->implode(',');
        $this->saveSettings($data);
        return back()->with('success', 'General settings updated');
    }

    private function saveSettings(array $values): void
    {
        $old = array_intersect_key(SchoolSetting::allValues(), $values);
        foreach ($values as $key => $value) {
            SchoolSetting::put($key, $value);
        }
        $changed = array_filter($values, fn ($v, $k) => (string) ($old[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
        if ($changed && function_exists('me_change_log')) {
            me_change_log('School settings changed', 'school.settings')
                ->record(array_intersect_key($old, $changed) ?: ['(new)' => ''], $changed);
        }
    }

    // Inventory
    public function productIndex()
    {
        $products = Product::with('category')->orderBy('id', 'desc')->paginate(20);
        $categories = \ME\SchoolManagement\Models\InventoryCategory::where('is_active', true)->get();
        return view('school-management::inventory.products', compact('products', 'categories'));
    }

    public function productStore(Request $request)
    {
        Product::create($this->validateProduct($request));
        return back()->with('success', 'Product created');
    }

    public function productUpdate(Request $request, $id)
    {
        Product::findOrFail($id)->update($this->validateProduct($request, $id));
        return back()->with('success', 'Product updated');
    }

    public function productDestroy($id)
    {
        $product = Product::findOrFail($id);
        return $this->deleteUnlessUsed($product, [
            'stock movements' => $this->uses('school_stocks', 'product_id', $id),
            'purchase lines'  => $this->uses('school_purchase_items', 'product_id', $id),
        ], $product->name, 'Product deleted');
    }

    public function stockIndex()
    {
        $stocks   = Stock::with('product')->orderBy('id', 'desc')->paginate(20);
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('school-management::inventory.stock', compact('stocks', 'products'));
    }

    public function stockStore(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:school_products,id',
            'type'       => 'required|in:in,out',
            'quantity'   => 'required|integer|min:1',
            'date'       => 'required|date',
            'note'       => 'nullable|string|max:1000',
        ]);

        // Keep the product's on-hand quantity in sync with stock movements.
        return DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            if ($data['type'] === 'out' && $data['quantity'] > (int) $product->quantity) {
                return back()->withInput()->withErrors(['quantity' => "Only {$product->quantity} {$product->name} in stock."]);
            }
            Stock::create($data);
            $data['type'] === 'in'
                ? $product->increment('quantity', $data['quantity'])
                : $product->decrement('quantity', $data['quantity']);
            return back()->with('success', 'Stock updated');
        });
    }

    public function purchaseIndex()
    {
        $purchases = Purchase::with(['supplier', 'items.product'])->orderBy('id', 'desc')->paginate(20);
        $products  = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'item_code', 'price']);
        $suppliers = Supplier::where('is_active', true)->get();
        return view('school-management::inventory.purchase', compact('purchases', 'suppliers', 'products'));
    }

    public function purchaseStore(Request $request)
    {
        $data = $request->validate([
            'invoice_no'         => 'required|string|max:255|unique:school_purchases,invoice_no',
            'supplier_id'        => 'nullable|exists:school_suppliers,id',
            'purchase_date'      => 'required|date',
            'paid_amount'        => 'nullable|numeric|min:0',
            'note'               => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:school_products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], ['items.required' => 'Add at least one item.']);

        $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
        $paid  = (float) ($data['paid_amount'] ?? 0);
        if ($paid > $total) {
            return back()->withInput()->with('error', 'Paid amount cannot exceed the purchase total.');
        }

        DB::transaction(function () use ($data, $total, $paid) {
            $purchase = Purchase::create([
                'invoice_no'    => $data['invoice_no'],
                'supplier_id'   => $data['supplier_id'] ?? null,
                'total_amount'  => $total,
                'paid_amount'   => $paid,
                'due_amount'    => $total - $paid,
                'purchase_date' => $data['purchase_date'],
                'payment_date'  => $paid > 0 ? $data['purchase_date'] : null,
                'status'        => 'received',
                'note'          => $data['note'] ?? null,
            ]);
            // Each line goes into stock.
            foreach ($data['items'] as $item) {
                $purchase->items()->create($item + ['total_price' => $item['quantity'] * $item['unit_price']]);
                Stock::create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'type'       => 'in',
                    'date'       => $data['purchase_date'],
                    'note'       => 'Purchase ' . $data['invoice_no'],
                ]);
                Product::whereKey($item['product_id'])->increment('quantity', $item['quantity']);
            }
        });

        return back()->with('success', 'Purchase saved and items added to stock');
    }

    public function purchaseDestroy($id)
    {
        $purchase = Purchase::with('items.product')->findOrFail($id);

        // Take the purchased stock back out; refuse if some was already used.
        foreach ($purchase->items as $item) {
            if ($item->product && $item->product->quantity < $item->quantity) {
                return back()->with('error', "Cannot delete: only {$item->product->quantity} {$item->product->name} left in stock, the purchase added {$item->quantity}.");
            }
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                if ($purchase->status === 'received' && $item->product) {
                    $item->product->decrement('quantity', $item->quantity);
                    Stock::create([
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'type'       => 'out',
                        'date'       => now()->toDateString(),
                        'note'       => 'Purchase ' . $purchase->invoice_no . ' deleted',
                    ]);
                }
            }
            $this->logDelete($purchase, 'Purchase ' . $purchase->invoice_no, fn () => $purchase->delete());
        });

        return back()->with('success', 'Purchase deleted and stock adjusted');
    }

    public function supplierIndex()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->paginate(20);
        return view('school-management::inventory.suppliers', compact('suppliers'));
    }

    public function supplierStore(Request $request)
    {
        Supplier::create($request->validate([
            'name'           => 'required|string|max:255',
            'company_name'   => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'required|string|max:50',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]));
        return back()->with('success', 'Supplier created');
    }

    public function supplierDestroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        return $this->deleteUnlessUsed($supplier, [
            'purchases' => $this->uses('school_purchases', 'supplier_id', $id),
        ], $supplier->name, 'Supplier deleted');
    }

    private function validateProduct(Request $request, $id = null): array
    {
        $data = $request->validate([
            'item_code'     => 'required|string|max:255|unique:school_products,item_code' . ($id ? ",$id" : ''),
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:school_inventory_categories,id',
            'unit'          => 'nullable|string|max:50',
            'price'         => 'nullable|numeric|min:0',
            'quantity'      => 'nullable|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
        ]);
        // NOT NULL columns with a default of 0.
        $data['quantity']      = $data['quantity'] ?? 0;
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        return $data;
    }
}
