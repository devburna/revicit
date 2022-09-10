<?php

namespace App\Policies;

use App\Models\StorefrontProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorefrontProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, StorefrontProduct $storefrontProduct)
    {
        return $user->is($storefrontProduct->storefront->company->user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, StorefrontProduct $storefrontProduct)
    {
        return $this->view($user, $storefrontProduct);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, StorefrontProduct $storefrontProduct)
    {
        return $this->view($user, $storefrontProduct);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, StorefrontProduct $storefrontProduct)
    {
        return $this->view($user, $storefrontProduct);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, StorefrontProduct $storefrontProduct)
    {
        return $this->view($user, $storefrontProduct);
    }
}
