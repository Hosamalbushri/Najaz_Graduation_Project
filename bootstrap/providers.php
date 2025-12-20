<?php

return [
    /**
     * Application service providers.
     */
    App\Providers\AppServiceProvider::class,

    /**
     * Webkul's service providers.
     */
    Webkul\Admin\Providers\AdminServiceProvider::class,
    // Webkul\Attribute\Providers\AttributeServiceProvider::class, // Disabled - Attribute module
    // Webkul\BookingProduct\Providers\BookingProductServiceProvider::class, // Disabled - depends on Product
    // Webkul\CMS\Providers\CMSServiceProvider::class, // Disabled - CMS module
    // Webkul\CartRule\Providers\CartRuleServiceProvider::class, // Disabled - depends on Product/Customer
    // Webkul\CatalogRule\Providers\CatalogRuleServiceProvider::class, // Disabled - depends on Product
    // Webkul\Category\Providers\CategoryServiceProvider::class, // Disabled - Category module
    // Webkul\Checkout\Providers\CheckoutServiceProvider::class, // Disabled - depends on Product/Sales/Customer
    Webkul\Core\Providers\CoreServiceProvider::class,
    Webkul\Core\Providers\EnvValidatorServiceProvider::class,
    // Webkul\Customer\Providers\CustomerServiceProvider::class, // Disabled - Customer module
    Webkul\DataGrid\Providers\DataGridServiceProvider::class,
    Webkul\DataTransfer\Providers\DataTransferServiceProvider::class,
//    Webkul\DebugBar\Providers\DebugBarServiceProvider::class,
    Webkul\FPC\Providers\FPCServiceProvider::class,
    // Webkul\GDPR\Providers\GDPRServiceProvider::class, // Disabled - depends on Customer
    // Webkul\Installer\Providers\InstallerServiceProvider::class, // Replaced with Najaz\Installer
    Najaz\Installer\Providers\InstallerServiceProvider::class,
    // Webkul\Inventory\Providers\InventoryServiceProvider::class, // Disabled - Inventory module
    Webkul\MagicAI\Providers\MagicAIServiceProvider::class,
    // Webkul\Marketing\Providers\MarketingServiceProvider::class, // Already disabled
    Webkul\Notification\Providers\NotificationServiceProvider::class,
    // Webkul\Payment\Providers\PaymentServiceProvider::class, // Disabled - depends on Sales
    // Webkul\Paypal\Providers\PaypalServiceProvider::class, // Disabled - depends on Payment
    // Webkul\Product\Providers\ProductServiceProvider::class, // Already disabled - Product module
    Webkul\Rule\Providers\RuleServiceProvider::class,
    // Webkul\Sales\Providers\SalesServiceProvider::class, // Already disabled - Sales module
    // Webkul\Shipping\Providers\ShippingServiceProvider::class, // Disabled - depends on Sales
    // Webkul\Shop\Providers\ShopServiceProvider::class, // Already disabled - Shop interface disabled
    Webkul\Sitemap\Providers\SitemapServiceProvider::class,
    // Webkul\SocialLogin\Providers\SocialLoginServiceProvider::class, // Disabled - depends on Customer
   Webkul\SocialShare\Providers\SocialShareServiceProvider::class,
   // Webkul\Tax\Providers\TaxServiceProvider::class, // Disabled - Tax module
    Webkul\Theme\Providers\ThemeServiceProvider::class,
    Webkul\User\Providers\UserServiceProvider::class,
];
