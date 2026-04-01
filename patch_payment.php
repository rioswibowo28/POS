<?php
$f = "resources/views/orders/payment.blade.php";
$c = file_get_contents($f);

// 1. Add qris vars
$t1 = "allowQrisSplitOnNoTax: {{ \App\Models\Setting::get('allow_qris_split_on_no_tax', '0') == '1' ? 'true' : 'false' }},";
$r1 = $t1 . "
          qrisCustomerDisplayEnabled: {{ \App\Models\Setting::get('enable_qris_customer_display', '0') == '1' ? 'true' : 'false' }},
          qrisImage1: '{{ \App\Models\Setting::get('qris_image_1') ? asset('storage/' . \App\Models\Setting::get('qris_image_1')) : null }}',
          qrisImage2: '{{ \App\Models\Setting::get('qris_image_2') ? asset('storage/' . \App\Models\Setting::get('qris_image_2')) : null }}',
";
$c = str_replace($t1, $r1, $c);

// 2. Add watcher
$t2 = "this.\$watch('isSplitPayment', value => {";
$r2 = "this.\$watch('paymentMethod', value => { this.syncToCustomerDisplay(); });\n              " . $t2;
$c = str_replace($t2, $r2, $c);

// 3. Add to sync payload (append after taxRate)
$t3 = "taxRate: this.activeTaxRate";
$r3 = $t3 . ",
                  showQris: this.paymentMethod === 'qris' && !this.isSplitPayment && this.qrisCustomerDisplayEnabled,
                  qrisImageUrl: this.flag ? this.qrisImage2 : this.qrisImage1";
$c = str_replace($t3, $r3, $c);

file_put_contents($f, $c);
echo "Payment patched\n";

