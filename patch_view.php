<?php
$f = "resources/views/settings/index.blade.php";
$c = file_get_contents($f);
$t = '<label class="block text-sm font-medium text-gray-700 mb-2">Customer Display Poster</label>';
$r = <<<EOT
                      <!-- QRIS Customer Display Feature -->
                      <div class="mt-8 mb-4 border-t pt-6">
                            <label class="flex items-center p-4 bg-gray-50 rounded-lg border-2 border-gray-200 hover:border-primary-300 cursor-pointer transition">
                                <input type="hidden" name="enable_qris_customer_display" value="0">
                                <input type="checkbox" name="enable_qris_customer_display" value="1" 
                                       {{ old("enable_qris_customer_display", \$settings["enable_qris_customer_display"] ?? "0") == "1" ? "checked" : "" }}
                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5 flex-shrink-0">
                                <div class="ml-3">
                                    <span class="text-sm font-semibold text-gray-900">Tampilkan QRIS di Customer Display</span>
                                    <p class="text-xs text-gray-600 mt-1">Jika aktif, saat kasir memilih pembayaran QRIS, kode QRIS akan muncul otomatis di layar Customer Display.</p>
                                </div>
                            </label>
                      </div>

                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- QRIS 1 -->
                            <div class="p-4 border rounded-lg bg-white">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gambar QRIS 1 (Normal)</label>
                                <input type="file" name="qris_image_1" accept="image/png,image/jpg,image/jpeg" class="input text-sm">
                                <p class="text-xs text-gray-500 mt-1">Ditampilkan saat pajak AKTIF (No-Tax tidak dicentang).</p>
                                @if(isset(\$settings["qris_image_1"]) && \$settings["qris_image_1"])
                                <div class="mt-3 border p-2 bg-gray-50 text-center rounded">
                                    <img src="{{ asset("storage/" . \$settings["qris_image_1"]) }}" class="h-32 mx-auto object-contain">
                                </div>
                                @endif
                            </div>

                            <!-- QRIS 2 -->
                            <div class="p-4 border rounded-lg bg-white">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gambar QRIS 2 (No-Tax)</label>
                                <input type="file" name="qris_image_2" accept="image/png,image/jpg,image/jpeg" class="input text-sm">
                                <p class="text-xs text-gray-500 mt-1">Ditampilkan saat pajak MATI (No-Tax dicentang).</p>
                                @if(isset(\$settings["qris_image_2"]) && \$settings["qris_image_2"])
                                <div class="mt-3 border p-2 bg-gray-50 text-center rounded">
                                    <img src="{{ asset("storage/" . \$settings["qris_image_2"]) }}" class="h-32 mx-auto object-contain">
                                </div>
                                @endif
                            </div>
                      </div>

                      <label class="block text-sm font-medium text-gray-700 mb-2">Customer Display Poster</label>
EOT;
$c = str_replace($t, $r, $c);
file_put_contents($f, $c);
echo "Settings patched\n";

