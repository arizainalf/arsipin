<?php

namespace App\Filament\Resources\ArsipResource\Widgets;

use App\Models\Arsip;
use Filament\Widgets\ChartWidget;

class LunasChart extends ChartWidget
{
    protected static ?string $heading = 'Lunas';

    protected function getData(): array
    {
        Arsip::where('status', '1')->get();
        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
