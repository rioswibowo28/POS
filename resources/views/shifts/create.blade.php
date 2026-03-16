@extends('layouts.app')

@section('title', 'Open New Shift')
@section('header', 'Open New Shift')

@section('content')
<div class="max-w-2xl mx-auto" x-data="shiftForm()">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-2">Membuka Shift Baru</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Hitung kas di kasir Anda sebelum memulai</li>
                        <li>• Shift saat ini akan otomatis ditetapkan: <strong>Shift {{ \App\Models\Shift::getShiftNumber() }}</strong></li>
                        <li>• Jam shift: 
                            @if(\App\Models\Shift::getShiftNumber() == 1)
                                05:00 - 17:00
                            @else
                                17:00 - 05:00
                            @endif
                        </li>
                        <li>• Semua pesanan tertunda akan dipindahkan ke shift ini</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <form action="{{ route('shifts.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="opening_cash" class="block text-sm font-medium text-gray-700 mb-2">
                    Kas Awal <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                    <input type="text" 
                           x-model="openingCashFormatted"
                           @input="handleOpeningCashInput()"
                           class="input pl-10 @error('opening_cash') border-red-500 @enderror" 
                           required
                           placeholder="0">
                    <input type="hidden" 
                           name="opening_cash" 
                           x-model="openingCash">
                </div>
                @error('opening_cash')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Masukkan jumlah kas yang ada di kasir Anda saat ini</p>
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          class="input @error('notes') border-red-500 @enderror"
                          placeholder="Catatan untuk shift ini...">{{ old('notes') }}</textarea>
                @error('notes')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Dibuka Oleh:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ auth()->user()->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ now()->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Waktu:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ now()->format('H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Nomor Shift:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ \App\Models\Shift::getShiftNumber() }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="{{ route('shifts.index') }}" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl shadow-lg hover:shadow-gray-300 hover:border-gray-400 transition-all duration-300">
                    <i class="fas fa-arrow-left mr-2.5 text-lg relative z-10"></i>
                    <span class="relative z-10">Kembali</span>
                    <span class="absolute inset-0 rounded-xl blur-xl bg-gray-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300 -z-10"></span>
                </a>
                <button type="submit" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-green-200 text-green-600 font-semibold rounded-xl shadow-lg hover:shadow-green-200 transition-all duration-300 overflow-hidden">
                    <span class="absolute inset-0 bg-gradient-to-r from-green-400 to-green-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                    <i class="fas fa-play mr-2.5 text-lg relative z-10 group-hover:text-white transition-colors duration-300"></i>
                    <span class="relative z-10 group-hover:text-white transition-colors duration-300">Buka Shift</span>
                    <span class="absolute inset-0 rounded-xl blur-xl bg-green-400 opacity-0 group-hover:opacity-30 transition-opacity duration-300 -z-10"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function shiftForm() {
    return {
        openingCash: {{ old('opening_cash', 0) }},
        openingCashFormatted: '',
        
        init() {
            this.openingCashFormatted = this.formatMoney(this.openingCash);
        },
        
        handleOpeningCashInput() {
            // Remove all non-digit characters
            let value = this.openingCashFormatted.replace(/\D/g, '');
            this.openingCash = parseInt(value) || 0;
            this.openingCashFormatted = this.formatMoney(this.openingCash);
        },
        
        formatMoney(amount) {
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    }
}
</script>

@endsection
