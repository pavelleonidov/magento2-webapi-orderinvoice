<?php
/**
 * Copyright © Pavel Leonidov. All rights reserved.
 */

namespace PavelLeonidov\WebApiOrderInvoice\Api;

/**
 * Class OrderInvoiceInterface
 *
 * @package PavelLeonidov\WebApiOrderInvoice\Api
 */
interface OrderInvoiceInterface
{
    /**
     * @param int $orderId
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     * @since 100.1.2
     */
    public function execute(
        $orderId
    );
}
