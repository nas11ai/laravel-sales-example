<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sales.create');
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->can('sales.edit') && $sale->isEditable();
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->can('sales.delete') && $sale->isEditable();
    }
}
