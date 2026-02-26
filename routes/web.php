<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\BdCompanyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentLocationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ForeignCompanyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobSectorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotepadController;
use App\Http\Controllers\OfficeStaffController;
use App\Http\Controllers\Acl\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\Reports\RefundReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard.data');

    // Notepad routes
    Route::get('/notepad', [NotepadController::class, 'index'])->name('notepad.index');
    Route::post('/notepad/setup', [NotepadController::class, 'setup'])->name('notepad.setup');
    Route::post('/notepad/unlock', [NotepadController::class, 'unlock'])->name('notepad.unlock');
    Route::post('/notepad/reset', [NotepadController::class, 'resetPassword'])->name('notepad.reset');
    Route::put('/notepad', [NotepadController::class, 'update'])->name('notepad.update');

    Route::get('/notifications/document-overdue', [NotificationController::class, 'documentOverdue'])
        ->name('notifications.document-overdue');
});

Route::middleware('auth')->group(function () {
    Route::middleware('permission:client.add')->group(function () {
        Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    });

    Route::middleware('permission:client.update')->group(function () {
        Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::post('clients/{client}/pay-vat', [ClientController::class, 'payVat'])->name('clients.pay-vat');
        Route::post('clients/{client}/unpay-vat', [ClientController::class, 'unpayVat'])->name('clients.unpay-vat');
        Route::post('clients/{client}/refund', [ClientController::class, 'refund'])->name('clients.refund');
    });

    Route::middleware('permission:client.delete')->group(function () {
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    Route::middleware('permission:client.view')->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/export/{type?}', [ClientController::class, 'export'])->name('clients.export');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    });

    Route::middleware('permission:quotation.add')->group(function () {
        Route::get('quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
    });

    Route::middleware('permission:quotation.update')->group(function () {
        Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    });

    Route::middleware('permission:quotation.view')->group(function () {
        Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('quotations/export/{type?}', [QuotationController::class, 'export'])->name('quotations.export');
        Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('quotations/{quotation}/download', [QuotationController::class, 'download'])->name('quotations.download');
    });

    Route::middleware('permission:invoice.add')->group(function () {
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    });

    Route::middleware('permission:invoice.update')->group(function () {
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    });

    Route::middleware('permission:invoice.view')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/export/{type?}', [InvoiceController::class, 'export'])->name('invoices.export');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    });

    Route::middleware('permission:agent.add')->group(function () {
        Route::get('agents/create', [AgentController::class, 'create'])->name('agents.create');
        Route::post('agents', [AgentController::class, 'store'])->name('agents.store');
    });

    Route::middleware('permission:agent.update')->group(function () {
        Route::get('agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
        Route::put('agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    });

    Route::middleware('permission:agent.view')->group(function () {
        Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
        Route::get('agents/export/{type?}', [AgentController::class, 'export'])->name('agents.export');
        Route::get('agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
    });

    Route::middleware('permission:bd-company.add')->group(function () {
        Route::get('bd-companies/create', [BdCompanyController::class, 'create'])->name('bd-companies.create');
        Route::post('bd-companies', [BdCompanyController::class, 'store'])->name('bd-companies.store');
    });

    Route::middleware('permission:bd-company.update')->group(function () {
        Route::get('bd-companies/{bdCompany}/edit', [BdCompanyController::class, 'edit'])->name('bd-companies.edit');
        Route::put('bd-companies/{bdCompany}', [BdCompanyController::class, 'update'])->name('bd-companies.update');
    });

    Route::middleware('permission:bd-company.view')->group(function () {
        Route::get('bd-companies', [BdCompanyController::class, 'index'])->name('bd-companies.index');
        Route::get('bd-companies/export/{type?}', [BdCompanyController::class, 'export'])->name('bd-companies.export');
        Route::get('bd-companies/{bdCompany}', [BdCompanyController::class, 'show'])->name('bd-companies.show');
    });

    Route::middleware('permission:foreign-company.add')->group(function () {
        Route::get('foreign-companies/create', [ForeignCompanyController::class, 'create'])->name('foreign-companies.create');
        Route::post('foreign-companies', [ForeignCompanyController::class, 'store'])->name('foreign-companies.store');
    });

    Route::middleware('permission:foreign-company.update')->group(function () {
        Route::get('foreign-companies/{foreignCompany}/edit', [ForeignCompanyController::class, 'edit'])->name('foreign-companies.edit');
        Route::put('foreign-companies/{foreignCompany}', [ForeignCompanyController::class, 'update'])->name('foreign-companies.update');
    });

    Route::middleware('permission:foreign-company.view')->group(function () {
        Route::get('foreign-companies', [ForeignCompanyController::class, 'index'])->name('foreign-companies.index');
        Route::get('foreign-companies/export/{type?}', [ForeignCompanyController::class, 'export'])->name('foreign-companies.export');
        Route::get('foreign-companies/{foreignCompany}', [ForeignCompanyController::class, 'show'])->name('foreign-companies.show');
        Route::get('foreign-companies/country/{country}', [ForeignCompanyController::class, 'country'])->name('foreign-companies.country');
    });

    Route::middleware('permission:office-staff.add')->group(function () {
        Route::get('office-staff/create', [OfficeStaffController::class, 'create'])->name('office-staff.create');
        Route::post('office-staff', [OfficeStaffController::class, 'store'])->name('office-staff.store');
    });

    Route::middleware('permission:office-staff.update')->group(function () {
        Route::get('office-staff/{officeStaff}/edit', [OfficeStaffController::class, 'edit'])->name('office-staff.edit');
        Route::put('office-staff/{officeStaff}', [OfficeStaffController::class, 'update'])->name('office-staff.update');
    });

    Route::middleware('permission:office-staff.view')->group(function () {
        Route::get('office-staff', [OfficeStaffController::class, 'index'])->name('office-staff.index');
        Route::get('office-staff/export/{type?}', [OfficeStaffController::class, 'export'])->name('office-staff.export');
        Route::get('office-staff/{officeStaff}', [OfficeStaffController::class, 'show'])->name('office-staff.show');
    });

    Route::middleware('permission:expense.add')->group(function () {
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });

    Route::middleware('permission:expense.update')->group(function () {
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    });

    Route::middleware('permission:expense.delete')->group(function () {
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    Route::middleware('permission:expense.view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    });

    Route::middleware('permission:document.view')->group(function () {
        Route::get('/clients/{client}/documents', [DocumentLocationController::class, 'show'])->name('clients.documents.show');
    });

    Route::middleware('permission:document.add')->group(function () {
        Route::post('/clients/{client}/documents', [DocumentLocationController::class, 'store'])->name('clients.documents.store');
    });

    Route::middleware('permission:document.update')->group(function () {
        Route::patch('/clients/{client}/documents/status', [DocumentLocationController::class, 'updateStatus'])->name('clients.documents.update-status');
    });

    Route::middleware('permission:document.update')->group(function () {
        Route::get('/document-holder-types', [\App\Http\Controllers\DocumentHolderTypeController::class, 'index'])
            ->name('document-holder-types.index');
        Route::post('/document-holder-types', [\App\Http\Controllers\DocumentHolderTypeController::class, 'store'])
            ->name('document-holder-types.store');
        Route::put('/document-holder-types/{documentHolderType}', [\App\Http\Controllers\DocumentHolderTypeController::class, 'update'])
            ->name('document-holder-types.update');
        Route::delete('/document-holder-types/{documentHolderType}', [\App\Http\Controllers\DocumentHolderTypeController::class, 'destroy'])
            ->name('document-holder-types.destroy');
    });

    Route::post('/job-sectors', [JobSectorController::class, 'store'])
        ->middleware('permission:job-sector.add')
        ->name('job-sectors.store');

    Route::middleware('permission:user.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware('permission:user.add')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('permission:user.update')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::middleware('permission:role.view')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('permission:role.add')->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware('permission:role.update')->group(function () {
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware('permission:role.delete')->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    });

    Route::middleware('permission:settings.update')->group(function () {
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // Reports Routes
    Route::middleware('permission:reports.refund-report')->group(function () {
        Route::get('/reports/refund-report', [RefundReportController::class, 'index'])->name('reports.refund-report.index');
        Route::get('/reports/refund-report/export', [RefundReportController::class, 'export'])->name('reports.refund-report.export');
    });

    // Accounting Routes
    Route::prefix('accounting')->name('accounting.')->middleware('permission:accounting.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Accounting\AccountingDashboardController::class, 'index'])->name('dashboard');

        // Subcategory Management Routes
        Route::get('/subcategories', [\App\Http\Controllers\SubcategoryController::class, 'index'])->name('subcategories.index');
        Route::post('/subcategories', [\App\Http\Controllers\SubcategoryController::class, 'store'])->name('subcategories.store');
        Route::put('/subcategories/{subcategory}', [\App\Http\Controllers\SubcategoryController::class, 'update'])->name('subcategories.update');
        Route::delete('/subcategories/{subcategory}', [\App\Http\Controllers\SubcategoryController::class, 'destroy'])->name('subcategories.destroy');

        // Income Routes
        Route::get('/income/travel-tourism', [\App\Http\Controllers\Accounting\IncomeController::class, 'showCategory'])->defaults('category', 'travel_tourism')->name('income.travel-tourism');
        Route::get('/income/manpower', [\App\Http\Controllers\Accounting\IncomeController::class, 'showCategory'])->defaults('category', 'manpower_exporting')->name('income.manpower');
        Route::get('/income/student', [\App\Http\Controllers\Accounting\IncomeController::class, 'showCategory'])->defaults('category', 'student_package')->name('income.student');
        Route::get('/income/other', [\App\Http\Controllers\Accounting\IncomeController::class, 'showCategory'])->defaults('category', 'other_income')->name('income.other');
        Route::post('/income', [\App\Http\Controllers\Accounting\IncomeController::class, 'store'])->name('income.store');
        Route::put('/income/{incomeEntry}', [\App\Http\Controllers\Accounting\IncomeController::class, 'update'])->name('income.update');
        Route::delete('/income/{incomeEntry}', [\App\Http\Controllers\Accounting\IncomeController::class, 'destroy'])->name('income.destroy');
        Route::get('/income/{category}/export/{type?}', [\App\Http\Controllers\Accounting\IncomeController::class, 'export'])->name('income.export');

        // Cost of Sales Routes
        Route::get('/cost-of-sales/travel-tourism', [\App\Http\Controllers\Accounting\CostOfSalesController::class, 'showCategory'])->defaults('category', 'travel_tourism')->name('cost-of-sales.travel-tourism');
        Route::get('/cost-of-sales/manpower', [\App\Http\Controllers\Accounting\CostOfSalesController::class, 'showCategory'])->defaults('category', 'manpower_exporting')->name('cost-of-sales.manpower');
        Route::get('/cost-of-sales/student', [\App\Http\Controllers\Accounting\CostOfSalesController::class, 'showCategory'])->defaults('category', 'student_package')->name('cost-of-sales.student');
        Route::post('/cost-of-sales', [\App\Http\Controllers\Accounting\CostOfSalesController::class, 'store'])->name('cost-of-sales.store');
        Route::put('/cost-of-sales/{costOfSale}', [\App\Http\Controllers\Accounting\CostOfSalesController::class, 'update'])->name('cost-of-sales.update');
        Route::delete('/cost-of-sales/{costOfSale}', [\App\Http\Controllers\Accounting\CostOfSalesController::class, 'destroy'])->name('cost-of-sales.destroy');
        Route::get('/cost-of-sales/{category}/export/{type?}', [\App\Http\Controllers\Accounting\CostOfSalesController::class, 'export'])->name('cost-of-sales.export');

        // Gross Profit Route
        Route::get('/gross-profit', [\App\Http\Controllers\Accounting\GrossProfitController::class, 'index'])->name('gross-profit');
        Route::get('/gross-profit/report', [\App\Http\Controllers\Accounting\GrossProfitController::class, 'report'])->name('gross-profit.report');

        // Operating Expenses Routes
        Route::get('/operating-expenses', fn() => redirect()->route('accounting.operating-expenses.employee'))->name('operating-expenses.index');
        Route::get('/operating-expenses/employee', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'showCategory'])->defaults('category', 'employee_manpower')->name('operating-expenses.employee');
        Route::get('/operating-expenses/administrative', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'showCategory'])->defaults('category', 'administrative')->name('operating-expenses.administrative');
        Route::get('/operating-expenses/selling-marketing', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'showCategory'])->defaults('category', 'selling_marketing')->name('operating-expenses.selling-marketing');
        Route::get('/operating-expenses/general', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'showCategory'])->defaults('category', 'general')->name('operating-expenses.general');
        Route::post('/operating-expenses', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'store'])->name('operating-expenses.store');
        Route::put('/operating-expenses/{operatingExpense}', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'update'])->name('operating-expenses.update');
        Route::delete('/operating-expenses/{operatingExpense}', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'destroy'])->name('operating-expenses.destroy');
        Route::get('/operating-expenses/{category}/report', [\App\Http\Controllers\Accounting\OperatingExpensesController::class, 'report'])->name('operating-expenses.report');

        // Operating Profit Route
        Route::get('/operating-profit', [\App\Http\Controllers\Accounting\OperatingProfitController::class, 'index'])->name('operating-profit');
        Route::get('/operating-profit/report', [\App\Http\Controllers\Accounting\OperatingProfitController::class, 'report'])->name('operating-profit.report');

        // Non-Operating Routes
        Route::get('/non-operating', [\App\Http\Controllers\Accounting\NonOperatingController::class, 'index'])->name('non-operating');
        Route::post('/non-operating', [\App\Http\Controllers\Accounting\NonOperatingController::class, 'store'])->name('non-operating.store');
        Route::put('/non-operating/{nonOperatingEntry}', [\App\Http\Controllers\Accounting\NonOperatingController::class, 'update'])->name('non-operating.update');
        Route::delete('/non-operating/{nonOperatingEntry}', [\App\Http\Controllers\Accounting\NonOperatingController::class, 'destroy'])->name('non-operating.destroy');
        Route::get('/non-operating/report', [\App\Http\Controllers\Accounting\NonOperatingController::class, 'report'])->name('non-operating.report');

        // Net Profit Before Tax Route
        Route::get('/net-profit-before-tax', [\App\Http\Controllers\Accounting\NetProfitBeforeTaxController::class, 'index'])->name('net-profit-before-tax');
        Route::get('/net-profit-before-tax/report', [\App\Http\Controllers\Accounting\NetProfitBeforeTaxController::class, 'report'])->name('net-profit-before-tax.report');

        // Tax Management Routes
        Route::get('/tax', [\App\Http\Controllers\Accounting\TaxController::class, 'index'])->name('tax');
        Route::post('/tax', [\App\Http\Controllers\Accounting\TaxController::class, 'store'])->name('tax.store');
        Route::put('/tax/{taxEntry}', [\App\Http\Controllers\Accounting\TaxController::class, 'update'])->name('tax.update');
        Route::delete('/tax/{taxEntry}', [\App\Http\Controllers\Accounting\TaxController::class, 'destroy'])->name('tax.destroy');
        Route::get('/tax/report', [\App\Http\Controllers\Accounting\TaxController::class, 'report'])->name('tax.report');

        // Tax Summary Routes
        Route::get('/tax-summary', [\App\Http\Controllers\Accounting\TaxSummaryController::class, 'index'])->name('tax-summary');
        Route::get('/tax-summary/report', [\App\Http\Controllers\Accounting\TaxSummaryController::class, 'report'])->name('tax-summary.report');
        Route::post('/tax-summary/{period}/payment', [\App\Http\Controllers\Accounting\TaxSummaryController::class, 'storePayment'])->name('tax-summary.payment.store');
        Route::delete('/tax-payment/{payment}', [\App\Http\Controllers\Accounting\TaxSummaryController::class, 'deletePayment'])->name('tax-payment.delete');

        // Tax Report Route
        Route::get('/tax-report', [\App\Http\Controllers\Accounting\TaxReportController::class, 'index'])->name('tax-report');
        Route::get('/tax-report/report', [\App\Http\Controllers\Accounting\TaxReportController::class, 'report'])->name('tax-report.report');

        // Net Profit After Tax Route
        Route::get('/net-profit-after-tax', [\App\Http\Controllers\Accounting\NetProfitAfterTaxController::class, 'index'])->name('net-profit-after-tax');
        Route::get('/net-profit-after-tax/report', [\App\Http\Controllers\Accounting\NetProfitAfterTaxController::class, 'report'])->name('net-profit-after-tax.report');

        // VAT Summary Route
        Route::get('/vat-summary', [\App\Http\Controllers\Accounting\VATSummaryController::class, 'index'])->name('vat-summary');
        Route::get('/vat-summary/report', [\App\Http\Controllers\Accounting\VATSummaryController::class, 'report'])->name('vat-summary.report');

        // VAT Payment Routes
        Route::post('/vat-summary/{period}/payment', [\App\Http\Controllers\Accounting\VATSummaryController::class, 'storePayment'])->name('vat-summary.payment.store');
        Route::delete('/vat-payment/{payment}', [\App\Http\Controllers\Accounting\VATSummaryController::class, 'deletePayment'])->name('vat-payment.delete');

        // VAT Report Route
        Route::get('/vat-report', [\App\Http\Controllers\Accounting\VATReportController::class, 'index'])->name('vat-report');
        Route::get('/vat-report/report', [\App\Http\Controllers\Accounting\VATReportController::class, 'report'])->name('vat-report.report');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
