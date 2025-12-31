<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderByDesc('paid_on')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Expense $expense) {
                return [
                    'id' => $expense->id,
                    'title' => $expense->title,
                    'amount' => $expense->amount,
                    'category' => $expense->category,
                    'paid_on' => optional($expense->paid_on)?->toDateString(),
                    'vendor' => $expense->vendor,
                    'notes' => $expense->notes,
                    'attachment_url' => $expense->attachment_path ? Storage::disk('public')->url($expense->attachment_path) : null,
                ];
            });

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
        ]);
    }

    public function create()
    {
        return Inertia::render('Expenses/Create', [
            'expense' => null,
            'mode' => 'create',
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('expenses/attachments', 'public');
        }

        unset($data['attachment']);

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        return Inertia::render('Expenses/Create', [
            'expense' => [
                'id' => $expense->id,
                'title' => $expense->title,
                'amount' => $expense->amount,
                'category' => $expense->category,
                'paid_on' => optional($expense->paid_on)?->toDateString(),
                'vendor' => $expense->vendor,
                'notes' => $expense->notes,
            ],
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('expenses/attachments', 'public');
        }

        unset($data['attachment']);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
