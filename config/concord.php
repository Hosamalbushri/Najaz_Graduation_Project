<?php

return [

    /**
     * The path of the convention file.
     */
    'convention' => Webkul\Core\CoreConvention::class,

    /**
     * Example:
     *
     * VendorA\ModuleX\Providers\ModuleServiceProvider::class,
     * VendorB\ModuleY\Providers\ModuleServiceProvider::class,
     */
    'modules' => [
        \Webkul\Admin\Providers\ModuleServiceProvider::class,
        // \Webkul\Attribute\Providers\ModuleServiceProvider::class, // Disabled - Attribute module
        // \Webkul\BookingProduct\Providers\ModuleServiceProvider::class, // Disabled - depends on Product
        // \Webkul\CMS\Providers\ModuleServiceProvider::class, // Disabled - CMS module
        // \Webkul\CartRule\Providers\ModuleServiceProvider::class, // Disabled - depends on Product/Customer
        // \Webkul\CatalogRule\Providers\ModuleServiceProvider::class, // Disabled - depends on Product
        // \Webkul\Category\Providers\ModuleServiceProvider::class, // Disabled - Category module
        // \Webkul\Checkout\Providers\ModuleServiceProvider::class, // Disabled - depends on Product/Sales/Customer
        \Webkul\Core\Providers\ModuleServiceProvider::class,
        // \Webkul\Customer\Providers\ModuleServiceProvider::class, // Disabled - Customer module
        \Webkul\DataGrid\Providers\ModuleServiceProvider::class,
        \Webkul\DataTransfer\Providers\ModuleServiceProvider::class,
        // \Webkul\GDPR\Providers\ModuleServiceProvider::class, // Disabled - depends on Customer
        // \Webkul\Inventory\Providers\ModuleServiceProvider::class, // Disabled - Inventory module
        // \Webkul\Marketing\Providers\ModuleServiceProvider::class, // Disabled - Marketing module
        \Webkul\Notification\Providers\ModuleServiceProvider::class,
        // \Webkul\Payment\Providers\ModuleServiceProvider::class, // Disabled - depends on Sales
        // \Webkul\Paypal\Providers\ModuleServiceProvider::class, // Disabled - depends on Payment
        // \Webkul\Product\Providers\ModuleServiceProvider::class, // Disabled - Product module
        \Webkul\Rule\Providers\ModuleServiceProvider::class,
        // \Webkul\Sales\Providers\ModuleServiceProvider::class, // Disabled - Sales module
        // \Webkul\Shipping\Providers\ModuleServiceProvider::class, // Disabled - depends on Sales
        // \Webkul\Shop\Providers\ModuleServiceProvider::class, // Disabled - depends on Product/Sales/Customer
        \Webkul\Sitemap\Providers\ModuleServiceProvider::class,
        // \Webkul\SocialLogin\Providers\ModuleServiceProvider::class, // Disabled - depends on Customer
        // \Webkul\Tax\Providers\ModuleServiceProvider::class, // Disabled - Tax module
        \Webkul\Theme\Providers\ModuleServiceProvider::class,
        \Webkul\User\Providers\ModuleServiceProvider::class,
        \Najaz\Citizen\Providers\ModuleServiceProvider::class,
        \Najaz\Request\Providers\ModuleServiceProvider::class,
        \Najaz\Notification\Providers\ModuleServiceProvider::class,
    ],

];
