# Manual Order Coupon Attribution For Siren and WooCommerce

This plugin makes it possible to manually attribute orders to collaborators when the coupon code in the created order is associated with a collaborator. The intent of this plugin is to provide a short-term solution for people who need to credit collaborators with sales when the source of the sale comes from outside of the website.

## Requirements

* Siren Affiliates 1.0.8 or higher.
* WooCommerce

## Setup

1. Create or update a program in Siren so that it uses "Coupon code used" engagement tracking event.
2. Create a coupon (you can set a zero-dollar discount on the coupon if you want). Be sure to associate the coupon with the collaborator you want to credit. [See this video](https://www.sirenaffiliates.com/documentation/getting-started/create-affiliate-coupons-for-woocommerce-using-siren/).
3. Install and activate this plugin
4. Go to WooCommerce>>Orders>>Add Order
5. Add the details of the order, and apply the coupon.
6. Click "Create Order"

Once the order is marked as "completed", you will see an obligation and engagement was added for the order.
