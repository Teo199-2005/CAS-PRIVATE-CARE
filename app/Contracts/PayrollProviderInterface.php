<?php

namespace App\Contracts;

/**
 * Future hook for Gusto (or another payroll provider) API sync.
 * Phase 1: manual export / copy from admin UI.
 */
interface PayrollProviderInterface
{
    /**
     * @param  array<string, mixed>  $payload  Sanitized employee + bank fields (no raw SSN in logs).
     * @return array<string, mixed>
     */
    public function syncEmployee(array $payload): array;
}
