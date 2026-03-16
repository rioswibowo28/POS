@extends('layouts.app')

@section('title', 'Close Shift')
@section('header', 'Close Shift - ' . $shift->shift_date->format('d M Y'))

@section('content')
<div class="max-w-3xl mx-auto" x-data="closeShiftForm()">
    @if($pendingOrders->count() > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3 mt-1"></i>
            <div>
                <h3 class="font-semibold text-yellow-900 mb-2">Peringatan Pesanan Tertunda</h3>
                <p class="text-sm text-yellow-800 mb-3">
                    Terdapat <strong>{{ $pendingOrders->count() }} pesanan tertunda</strong> di shift ini.
                    Pesanan ini akan otomatis dipindahkan ke shift berikutnya.
                </p>
                <div class="space-y-1">
                    @foreach($pendingOrders as $order)
                    <div class="text-sm text-yellow-800">
                        • {{ $order->order_number }} - {{ $order->table ? 'Meja ' . $order->table->number : ucfirst($order->type->value) }} 
                        (Rp {{ number_format($order->total, 0, ',', '.') }})
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Shift</h3>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="border rounded-lg p-4">
                <p class="text-gray-600 text-sm mb-1">Total Penjualan</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($shift->payments()->where('status', 'paid')->sum('amount'), 0, ',', '.') }}</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-gray-600 text-sm mb-1">Total Pesanan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $shift->orders()->count() }}</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-gray-600 text-sm mb-1">Kas Awal</p>
                <p class="text-xl font-bold text-gray-900">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-gray-600 text-sm mb-1">Penjualan Tunai</p>
                <p class="text-xl font-bold text-gray-900">Rp {{ number_format($shift->payments()->where('method', 'cash')->where('status', 'paid')->sum('amount'), 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="border-t pt-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-900 font-semibold">Kas yang Diharapkan:</span>
                <span class="text-2xl font-bold text-primary-600">Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Form Tutup Shift</h3>
        
        <form action="{{ route('shifts.close', $shift) }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="closing_cash" class="block text-sm font-medium text-gray-700 mb-2">
                    Jumlah Kas Aktual <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                    <input type="text" 
                           x-model="closingCashFormatted"
                           @input="handleClosingCashInput()"
                           class="input pl-10 text-lg font-semibold @error('closing_cash') border-red-500 @enderror" 
                           required
                           placeholder="0">
                    <input type="hidden" 
                           name="closing_cash" 
                           x-model="closingCash">
                </div>
                @error('closing_cash')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Hitung semua uang kas di kasir dan masukkan jumlah total</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700 font-medium">Selisih Kas:</span>
                    <span id="difference" class="text-2xl font-bold">Rp 0</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Positif = Kelebihan kas | Negatif = Kekurangan kas</p>
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan Penutupan (Opsional)
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          class="input @error('notes') border-red-500 @enderror"
                          placeholder="Catatan atau masalah selama shift ini...">{{ old('notes') }}</textarea>
                @error('notes')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Sebelum menutup:</p>
                        <ul class="space-y-1">
                            <li>• Pastikan semua kas telah dihitung dengan akurat</li>
                            <li>• Verifikasi semua pesanan untuk shift ini telah diproses</li>
                            <li>• Setelah ditutup, tindakan ini tidak dapat dibatalkan</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="{{ route('shifts.show', $shift) }}" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl shadow-lg hover:shadow-gray-300 hover:border-gray-400 transition-all duration-300">
                    <i class="fas fa-arrow-left mr-2.5 text-lg relative z-10"></i>
                    <span class="relative z-10">Kembali</span>
                    <span class="absolute inset-0 rounded-xl blur-xl bg-gray-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300 -z-10"></span>
                </a>
                <button type="submit" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-orange-200 text-orange-600 font-semibold rounded-xl shadow-lg hover:shadow-orange-200 transition-all duration-300 overflow-hidden">
                    <span class="absolute inset-0 bg-gradient-to-r from-orange-400 to-orange-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                    <i class="fas fa-check-circle mr-2.5 text-lg relative z-10 group-hover:text-white transition-colors duration-300"></i>
                    <span class="relative z-10 group-hover:text-white transition-colors duration-300">Tutup Shift</span>
                    <span class="absolute inset-0 rounded-xl blur-xl bg-orange-400 opacity-0 group-hover:opacity-30 transition-opacity duration-300 -z-10"></span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const expectedCash = {{ $expectedCash }};

function closeShiftForm() {
    return {
        closingCash: {{ old('closing_cash', $expectedCash) }},
        closingCashFormatted: '',
        
        init() {
            this.closingCashFormatted = this.formatMoney(this.closingCash);
            this.calculateDifference();
            
            // Watch for changes in closingCash
            this.$watch('closingCash', () => {
                this.calculateDifference();
            });
        },
        
        handleClosingCashInput() {
            // Remove all non-digit characters
            let value = this.closingCashFormatted.replace(/\D/g, '');
            this.closingCash = parseInt(value) || 0;
            this.closingCashFormatted = this.formatMoney(this.closingCash);
        },
        
        formatMoney(amount) {
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        
        calculateDifference() {
            const difference = this.closingCash - expectedCash;
            const differenceEl = document.getElementById('difference');
            
            let color = 'text-green-600';
            if (difference > 0) color = 'text-blue-600';
            if (difference < 0) color = 'text-red-600';
            
            differenceEl.className = 'text-2xl font-bold ' + color;
            differenceEl.textContent = (difference >= 0 ? '+' : '') + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(difference));
        }
    }
}
</script>
@endpush
@endsection
