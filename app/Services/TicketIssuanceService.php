<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketIssuanceService
{
    public function generateCode(Category $category, bool $isPriority): string
    {
        $prefix = strtoupper($category->prefix);

        $lastTicket = Ticket::where('category_id', $category->id)
            ->whereDate('created_at', now()->today())
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $nextNumber = 1;

        if ($lastTicket) {
            $parts = explode('-', $lastTicket->code);
            $nextNumber = ((int) end($parts)) + 1;
        }

        $sequence = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return $isPriority 
            ? "P-{$prefix}-{$sequence}" 
            : "{$prefix}-{$sequence}";
    }

    public function issueTicket(array $data): Ticket
    {
        if (! empty($data['idempotency_key'])) {
            $existingTicket = Ticket::where('idempotency_key', $data['idempotency_key'])->first();
            
            if ($existingTicket) {
                return $existingTicket;
            }
        }

        try {
            return DB::transaction(function () use ($data) {
                $category = Category::findOrFail($data['category_id']);
                $isPriority = (bool) ($data['is_priority'] ?? false);

                $data['code'] = $this->generateCode($category, $isPriority);

                return Ticket::create($data);
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e) && ! empty($data['idempotency_key'])) {
                $existingTicket = Ticket::where('idempotency_key', $data['idempotency_key'])->first();
                
                if ($existingTicket) {
                    return $existingTicket;
                }
            }

            Log::error("Failed to issue ticket: {$e->getMessage()}", $data);
            throw $e;
        }
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return in_array($e->getCode(), ['23000', '23505', 1062, 19]);
    }
}
