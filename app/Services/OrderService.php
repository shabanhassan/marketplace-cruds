<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $merchant   = Merchant::where('domain', '=', $data['merchant_domain'])->get()->first();

        $affiliate = Affiliate::whereHas('User', function($query) use ($data) {
            $query->whereLike('email', $data['customer_email']);
        })->get()->first();

        // $affiliate  = Affiliate::where('discount_code', $data['discount_code'])->get()->first();

        if(!$affiliate){
            $user   = new User;

            $user->name     = $data['customer_name'];
            $user->email    = $data['customer_email'];
            $user->type     = User::TYPE_AFFILIATE;

            $user->save();

            $affiliate  = User::find($user->id)->affiliate()->save(new Affiliate(array(
                'merchant_id'       => $merchant->id,
                'commission_rate'   => $merchant->default_commission_rate,
                'discount_code'     => $data['discount_code'],
            )));

            // $affiliate  = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $merchant->default_commission_rate, $data['discount_code']);
        } 
        
        // $order = Order::updateOrCreate([
        //     'external_order_id'=> $data['order_id']
        // ],
        // [
        //     'subtotal'      => $data['subtotal_price'],
        //     'commission_owed'   => $data['subtotal_price'] * $affiliate->commission_rate,
        //     'merchant_id'   => $merchant->id,
        //     'affiliate_id'  => $affiliate->id
        // ]);

        $order = Order::where('external_order_id', $data['order_id'])->get()->first();

        if(!$order){
            $order  = new Order;
            $order->external_order_id   = $data['order_id'];
        } 

        $order->subtotal            = $data['subtotal_price'];
        $order->commission_owed     = $data['subtotal_price'] * $affiliate->commission_rate;

        $order->merchant_id            = $merchant->id;
        $order->affiliate_id           = $affiliate->id;

        $order->save();
    }
}
