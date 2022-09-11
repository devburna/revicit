<?php

namespace App\Policies;

use App\Models\StorefrontOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorefrontOrderPolicy
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
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, StorefrontOrder $storefrontOrder)
    {
        return $user->is($storefrontOrder->storefront->company->user);
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
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, StorefrontOrder $storefrontOrder)
    {
        return $this->view($user, $storefrontOrder);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, StorefrontOrder $storefrontOrder)
    {
        return $this->view($user, $storefrontOrder);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, StorefrontOrder $storefrontOrder)
    {
        return $this->view($user, $storefrontOrder);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, StorefrontOrder $storefrontOrder)
    {
        return $this->view($user, $storefrontOrder);
    }
}
