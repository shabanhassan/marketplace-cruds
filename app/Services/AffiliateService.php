<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate, ?string $dCode = null): Affiliate
    {
        $alreadyMerchant = Merchant::whereHas('User', function($query) use ($email) {
            $query->where('email', '=', $email);
        })->get()->first();

        if($alreadyMerchant) {
            throw new AffiliateCreateException('User is already a merchant');
        }

        $alreadyAffiliate = Affiliate::whereHas('User', function($query) use ($email) {
            $query->where('email', '=', $email);
        })->get()->first();

        if($alreadyAffiliate) {
            throw new AffiliateCreateException('User is already an affiliate');
        }

        if(is_null($dCode)){
            $discountCode   = new ApiService;
            
            $discountCode   = $discountCode->createDiscountCode($merchant);
        } else {
            $discountCode   = array('code' => $dCode);
        }

        $user   = new User;

        $user->name     = $name;
        $user->email    = $email;
        $user->type     = User::TYPE_AFFILIATE;

        $user->save();

        $affiliate  = User::find($user->id)->affiliate()->save(new Affiliate(array(
            'merchant_id'       => $merchant->id,
            'commission_rate'   => $commissionRate,
            'discount_code'     => $discountCode['code'],
        )));

        Mail::to($affiliate->user)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
