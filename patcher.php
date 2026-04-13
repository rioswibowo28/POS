<?php
$file = 'app/Http/Controllers/Web/ReportController.php';
$content = file_get_contents($file);

$calcCode = <<<PHP
        \$normalCash = \$normalOrders->sum(function(\$order) {
            return \$order->payments->where('method', \App\Enums\PaymentMethod::CASH)->sum('amount');
        });
        \$normalQris = \$normalOrders->sum(function(\$order) {
            return \$order->payments->where('method', \App\Enums\PaymentMethod::QRIS)->sum('amount');
        });
        \$tempCash = \$tempOrders->where('payment_method', 'cash')->sum('total');
        \$tempQris = \$tempOrders->where('payment_method', 'qris')->sum('total');

        \$summary = [
            'all_count' => \$normalOrders->count() + \$tempOrders->count(),
            'all_subtotal' => \$normalOrders->sum('subtotal') + \$tempOrders->sum('subtotal'),
            'all_tax' => \$normalOrders->sum('tax_amount') + \$tempOrders->sum('tax_amount'),
            'all_discount' => \$normalOrders->sum('discount') + \$tempOrders->sum('discount'),
            'all_total' => \$normalOrders->sum('total') + \$tempOrders->sum('total'),
            'all_cash' => \$normalCash + \$tempCash,
            'all_qris' => \$normalQris + \$tempQris,

            'normal_count' => \$normalOrders->count(),
            'normal_subtotal' => \$normalOrders->sum('subtotal'),
            'normal_tax' => \$normalOrders->sum('tax_amount'),
            'normal_discount' => \$normalOrders->sum('discount'),
            'normal_total' => \$normalOrders->sum('total'),
            'normal_cash' => \$normalCash,
            'normal_qris' => \$normalQris,
            
            'temp_count' => \$tempOrders->count(),
            'temp_subtotal' => \$tempOrders->sum('subtotal'),
            'temp_tax' => \$tempOrders->sum('tax_amount'),
            'temp_discount' => \$tempOrders->sum('discount'),
            'temp_total' => \$tempOrders->sum('total'),
            'temp_cash' => \$tempCash,
            'temp_qris' => \$tempQris,
        ];
PHP;

$pattern = '/\s*\$summary\s*=\s*\[\s*\'all_count\'[^\]]+?\'temp_total\'\s*=>\s*\$tempOrders->sum\(\'total\'\),\s*\];/s';

$newContent = preg_replace($pattern, "\n" . $calcCode, $content);

file_put_contents($file, $newContent);
echo "Replaced " . preg_match_all($pattern, $content) . " occurrences.";
