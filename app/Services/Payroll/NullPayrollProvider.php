<?php

namespace App\Services\Payroll;

use App\Contracts\PayrollProviderInterface;

class NullPayrollProvider implements PayrollProviderInterface
{
    public function syncEmployee(array $payload): array
    {
        return [
            'synced' => false,
            'provider' => 'none',
            'message' => 'External payroll API is not configured.',
        ];
    }
}
