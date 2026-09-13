<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * iMenu 2026 - تقرير مبيعات الكوفي.
 *
 * FinancesExport الأصلي يصدّر ٢٣ عمودًا لمنصة توصيل بعمولة (السائق، العنوان،
 * رسوم المنصة، معالج الدفع، Stripe) — كلها فارغة عندنا. هذا التقرير يصدّر ما
 * يخصّ كافيه الاستلام فقط، بعناوين عربية. الأصلي باقٍ كما هو لصفحة المدير.
 */
class SalesExport implements FromArray, WithHeadings
{
    protected $rows;

    protected $withVat;

    public function __construct(array $rows, bool $withVat = false)
    {
        $this->rows = $rows;
        $this->withVat = $withVat;
    }

    public function headings(): array
    {
        $headings = [
            __('Order'),
            __('Date'),
            __('Time'),
            __('Pickup method'),
            __('Items'),
        ];

        if ($this->withVat) {
            $headings[] = __('VAT');
            $headings[] = __('Net');
        }

        $headings[] = __('Amount');

        return $headings;
    }

    public function array(): array
    {
        return $this->rows;
    }
}
