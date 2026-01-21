<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any product.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine if the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->role === 'ADMIN';
    }
}