<?php
$f = "resources/views/pos/customer-display.blade.php";
$c = file_get_contents($f);

// 1. Add variables
$t1 = "orderNumber: '',";
$r1 = $t1 . "\n            showQris: false,\n            qrisImageUrl: null,";
$c = str_replace($t1, $r1, $c);

// 2. Add to reset payload
$t2 = "customerName: '', mode: '', orderNumber: ''";
$r2 = "customerName: '', mode: '', orderNumber: '', showQris: false, qrisImageUrl: null";
$c = str_replace($t2, $r2, $c);

// 3. Update loadData
$t3 = "this.orderNumber = data.orderNumber || '';";
$r3 = $t3 . "\n                this.showQris = data.showQris || false;\n                this.qrisImageUrl = data.qrisImageUrl || null;";
$c = str_replace($t3, $r3, $c);

// 4. Update HTML template
$t4 = "<template x-if=\"posterImage\">
            <img :src=\"posterImage\" alt=\"Advertisement\" />
        </template>
        <template x-if=\"!posterImage\">";
$r4 = "<template x-if=\"showQris && qrisImageUrl != null && qrisImageUrl != ''\">
            <div class=\"flex flex-col items-center justify-center p-8 bg-white w-full h-full\">
                <h2 class=\"text-4xl font-bold mb-8 text-gray-800\">Scan QRIS Untuk Membayar</h2>
                <img :src=\"qrisImageUrl\" alt=\"QRIS\" class=\"max-w-full max-h-[60%] object-contain shadow-2xl rounded-2xl border-8 border-gray-100\" />
                <div class=\"mt-10 flex items-center justify-center space-x-3 text-3xl text-gray-700 bg-gray-50 px-10 py-5 rounded-full font-bold border-4 border-gray-200 shadow-inner\">
                    <span class=\"text-gray-500 mr-2\">TOTAL:</span>
                    <span class=\"text-green-600\">Rp <span x-text=\"formatMoney(total)\"></span></span>
                </div>
            </div>
        </template>
        <template x-if=\"!(showQris && qrisImageUrl != null && qrisImageUrl != '')\">
            <div class=\"w-full h-full flex items-center justify-center\">
                <template x-if=\"posterImage\">
                    <img :src=\"posterImage\" alt=\"Advertisement\" class=\"w-full h-full object-cover\" />
                </template>
                <template x-if=\"!posterImage\">";
$c = str_replace($t4, $r4, $c);

// 5. Close inner wrapper container
$t5 = "</div>
        </template>
    </div>

    <!-- Customer Display (Right Side) -->";
$r5 = "</div>
                </template>
            </div>
        </template>
    </div>

    <!-- Customer Display (Right Side) -->";
$c = str_replace($t5, $r5, $c);

file_put_contents($f, $c);
echo "Customer display patched\n" . substr_count($c, "showQris");

