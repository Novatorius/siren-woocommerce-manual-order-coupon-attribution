<?php
/*
 * Plugin Name: Siren Affiliates: Manual Order Coupon Attribution For WooCommerce
 * Description: Extends Siren so that WooCommerce orders manually added with coupon codes be attributed to collaborators.
 * Author: Novatorius, LLC
 * Author URI: https://sirenaffiliates.com
 * Version: 1.0.0
 */

use PHPNomad\Core\Facades\Event;
use PHPNomad\Core\Facades\InstanceProvider;
use PHPNomad\Database\Exceptions\RecordNotFoundException;
use PHPNomad\Datastore\Exceptions\DatastoreErrorException;
use PHPNomad\Di\Exceptions\DiException;
use PHPNomad\Utils\Helpers\Arr;
use Siren\Commerce\Events\CouponApplied;
use Siren\Commerce\Events\SaleTriggered;
use Siren\Commerce\Events\TransactionCompleted;
use Siren\Mappings\Core\Facades\Mappings;
use Siren\Opportunities\Core\Interfaces\OpportunityLocatorService;
use Siren\Opportunities\Core\Locators\OpportunityFromMappingTable;
use Siren\Opportunities\Core\Services\OpportunityCreateOrUpdateService;
use Siren\Transactions\Core\Facades\Transactions;
use Siren\WordPress\Extensions\WooCommerce\Adapters\OrderToTransactionDetailsAdapter;

add_action('siren_ready', function () {
    add_action('woocommerce_new_order', function ($orderId) {
        // Get instances
        try {
            $createOrUpdateService = InstanceProvider::get(OpportunityCreateOrUpdateService::class);
            $opportunityLocator = InstanceProvider::get(OpportunityLocatorService::class);
            $detailsAdapter = InstanceProvider::get(OrderToTransactionDetailsAdapter::class);
        } catch (DiException $e) {
            return;
        }

        $userId = get_current_user_id();
        $order = wc_get_order($orderId);
        $customerId = $order->get_user_id();
        $couponCodes = Arr::pluck($order->get_coupons(), 'code');

        // Detect manually added orders
        if (!empty($couponCodes) && $userId > 0 && $customerId !== $userId) {
            $opportunity = $opportunityLocator->locateUsing([
                'locator' => OpportunityFromMappingTable::getId(),
                'transformer' => fn(OpportunityFromMappingTable $instance) => $instance->setExternalId($customerId)->setExternalType('user')
            ]);

            // Maybe create an opportunity if one could not be found from the customer.
            $opportunity = $createOrUpdateService->createOrUpdateOpportunity($opportunity);

            // Apply all coupons to the bound opportunity.
            foreach($couponCodes as $couponCode){
                Event::broadcast(new CouponApplied($couponCode, $opportunity));
            }

            // broadcast the sale triggered event.
            Event::broadcast(new SaleTriggered($opportunity->getId(), $detailsAdapter->toArray($orderId), $orderId, 'wc_order'));
        }
    });
});
