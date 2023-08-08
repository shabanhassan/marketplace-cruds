<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        $user   = new User;

        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->password = $data['api_key'];
        $user->type     = User::TYPE_MERCHANT;

        $user->save();

        return User::find($user->id)->merchant()->save(new Merchant(array(
            'domain'    => $data['domain'],
            'display_name'  => $data['name'],
        )));
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method

        $user->merchant()->update([
            'domain'    => $data['domain'],
            'display_name'  => $data['name'],
        ]);
        
        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            'password'  => $data['api_key'],
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method

        $merchant   = Merchant::whereHas('User', function($query) use ($email) {
            $query->where('email', '=', $email);
        })->get()->first();

        return $merchant;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
    }
}
