<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionIndex extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterType = 'all';

    public $totalIncome = 0;
    public $totalExpense = 0;

    // Modal state
    public $isOpen = false;

    // Form fields
    public $transactionId;
    public $type;
    public $title;
    public $amount;
    public $date;
    public $description;
    public $image;
    public $existingImage;

    // Chart
    public $chartLabels = [];
    public $chartIncome = [];
    public $chartExpense = [];

    protected $listeners = [
        'deleteConfirmed' => 'delete',
    ];

    public function mount()
    {
        // initialize chart data (no year filter)
        $this->updateChartData();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->updateChartData();
        $this->dispatch('chart-updated');
    }

    public function openForm()
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    public function edit($id)
    {
        $tx = Transaction::where('user_id', Auth::id())->findOrFail($id);

        $this->transactionId = $tx->id;
        $this->type = $tx->type;
        $this->title = $tx->title;
        $this->amount = $tx->amount;
        $this->date = $tx->date->format('Y-m-d');
        $this->description = $tx->description;
        $this->existingImage = $tx->image;

        $this->isOpen = true;
    }

    public function delete($id)
    {
        $tx = Transaction::where('user_id', Auth::id())->findOrFail($id);

        if ($tx->image && Storage::disk('public')->exists($tx->image)) {
            Storage::disk('public')->delete($tx->image);
        }

        $tx->delete();

        $this->dispatch('swal:alert', [
            'type' => 'success',
            'message' => 'Data berhasil dihapus'
        ]);

        $this->updateChartData();
        $this->dispatch('chart-updated');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Konfirmasi Hapus',
            'text' => 'Apakah Anda yakin ingin menghapus data ini?',
            'icon' => 'warning',
            'confirmButtonText' => 'Ya',
            'cancelButtonText' => 'Tidak',
            'onConfirmed' => 'deleteConfirmed',
            'onCancelled' => 'cancelDelete',
            'data' => $id
        ]);
    }

    public function cancelDelete()
    {
        $this->dispatch('swal:alert', [
            'type' => 'info',
            'message' => 'Penghapusan dibatalkan'
        ]);
    }

    public function closeForm()
    {
        $this->resetForm();
        $this->isOpen = false;
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|in:income,expense',
            'title' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'type' => $this->type,
            'title' => $this->title,
            'amount' => $this->amount,
            'date' => $this->date,
            'description' => $this->description,
        ];

        if ($this->image) {
            $imageName = time() . '_' . $this->image->getClientOriginalName();
            $imageName = str_replace(' ', '_', $imageName);
            $imagePath = $this->image->storeAs('transactions', $imageName, 'public');

            if ($this->existingImage && Storage::disk('public')->exists($this->existingImage)) {
                Storage::disk('public')->delete($this->existingImage);
            }

            $data['image'] = $imagePath;
        } elseif ($this->transactionId) {
            $tx = Transaction::find($this->transactionId);
            if ($tx && $tx->image) {
                $data['image'] = $tx->image;
            }
        }

        Transaction::updateOrCreate(['id' => $this->transactionId], $data);

        $this->dispatch('swal:alert', [
            'type' => 'success',
            'message' => $this->transactionId ? 'Data berhasil diedit' : 'Data berhasil ditambah'
        ]);

        $this->closeForm();
        $this->updateChartData();
        $this->dispatch('chart-updated');
    }

    public function removeImage()
    {
        if ($this->existingImage && Storage::disk('public')->exists($this->existingImage)) {
            Storage::disk('public')->delete($this->existingImage);
        }

        $this->existingImage = null;
        $this->image = null;

        $this->dispatch('swal:alert', [
            'type' => 'success',
            'message' => 'Bukti berhasil dihapus'
        ]);

        $this->updateChartData();
        $this->dispatch('chart-updated');
    }

    private function resetForm()
    {
        $this->transactionId = null;
        $this->type = '';
        $this->title = '';
        $this->amount = '';
        $this->date = '';
        $this->description = '';
        $this->image = null;
        $this->existingImage = null;
    }

    private function updateChartData()
    {
        $this->loadYearlyChart();
    }

    private function loadYearlyChart()
    {
        // Aggregate by month (across all years) so the chart shows month trends without a year filter
        $monthly = Transaction::select(
            DB::raw("MONTH(date) as month"),
            DB::raw("SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income"),
            DB::raw("SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense")
        )
        ->where('user_id', Auth::id())
        ->when($this->filterType !== 'all', fn($q) => $q->where('type', $this->filterType))
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->keyBy('month');

        $labels = [];
        $income = [];
        $expense = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::create(2000, $m)->format('M');
            $income[] = $monthly[$m]->income ?? 0;
            $expense[] = $monthly[$m]->expense ?? 0;
        }

        $this->chartLabels = $labels;
        $this->chartIncome = $income;
        $this->chartExpense = $expense;
    }

    public function render()
    {
        $query = Transaction::where('user_id', Auth::id());

        if ($this->search) $query->where('title', 'like', '%' . $this->search . '%');
        if ($this->filterType !== 'all') $query->where('type', $this->filterType);


        $transactions = $query->orderBy('date', 'desc')->paginate(20);

        // compute totals (no year filter)
        $this->totalIncome = Transaction::where('user_id', Auth::id())->where('type', 'income')->sum('amount');
        $this->totalExpense = Transaction::where('user_id', Auth::id())->where('type', 'expense')->sum('amount');

        return view('livewire.transaction-index', [
            'transactions' => $transactions,
            'chartLabels' => $this->chartLabels,
            'chartIncome' => $this->chartIncome,
            'chartExpense' => $this->chartExpense,
        ]);
    }
}
