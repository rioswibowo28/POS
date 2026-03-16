@extends('layouts.app')

@section('title', 'Backup Database')
@section('header', 'Backup Database')

@section('content')
<div class="space-y-6">

    {{-- Alert --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-times-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Backup Database</h2>
            <p class="text-gray-500 text-sm mt-1">Kelola backup database aplikasi</p>
        </div>
        <form action="{{ route('backups.create') }}" method="POST" id="backupForm">
            @csrf
            <button type="submit" id="btnBackup"
                class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-lg flex items-center gap-2 transition shadow-sm font-medium">
                <i class="fas fa-download"></i>
                <span>Backup Sekarang</span>
            </button>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-database text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Backup</p>
                    <p class="text-xl font-bold text-gray-800">{{ count($backups) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Backup Terakhir</p>
                    <p class="text-sm font-bold text-gray-800">{{ count($backups) > 0 ? $backups[0]['date'] : '-' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hdd text-orange-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Ukuran</p>
                    <p class="text-xl font-bold text-gray-800">{{ $totalSizeDisplay }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Informasi Auto Backup</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Lokasi backup: <strong>{{ $backupPath }}</strong></li>
                    <li>Jadwal Backup 1: <strong>{{ $schedule1 }}</strong> 
                        @if($schedule1Enabled)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                        @endif
                    </li>
                    <li>Jadwal Backup 2: <strong>{{ $schedule2 }}</strong> 
                        @if($schedule2Enabled)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                        @endif</li>
                    <li>Backup lama dihapus otomatis setelah <strong>{{ $keepDays }} hari</strong></li>
                    <li>Download backup secara berkala ke flashdisk/cloud untuk keamanan</li>
                    <li>Ubah pengaturan di menu <a href="{{ route('settings.index') }}" class="underline font-semibold">Settings</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-folder text-gray-400"></i>
                Daftar Backup
            </h3>
        </div>

        @if(count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase w-12">No</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Nama File</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Ukuran</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($backups as $index => $backup)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-file-alt text-gray-400"></i>
                                    <span class="text-sm font-medium text-gray-700">{{ $backup['name'] }}</span>
                                    @if($index === 0)
                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-medium">Terbaru</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $backup['size'] }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $backup['date'] }}</td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('backups.download', $backup['name']) }}"
                                       class="bg-green-50 text-green-600 hover:bg-green-100 px-3 py-1.5 rounded-lg text-xs font-medium transition inline-flex items-center gap-1">
                                        <i class="fas fa-download text-xs"></i>
                                        Download
                                    </a>
                                    <form action="{{ route('backups.destroy', $backup['name']) }}" method="POST"
                                          onsubmit="return confirm('Hapus backup {{ $backup['name'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-medium transition inline-flex items-center gap-1">
                                            <i class="fas fa-trash text-xs"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-16 text-center">
                <i class="fas fa-database text-gray-200 text-5xl mb-4"></i>
                <p class="text-gray-500 font-medium">Belum ada backup</p>
                <p class="text-gray-400 text-sm mt-1">Klik "Backup Sekarang" untuk membuat backup pertama</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('backupForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnBackup');
        btn.disabled = true;
        btn.classList.add('opacity-75');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Memproses...</span>';
    });
</script>
@endpush
@endsection
