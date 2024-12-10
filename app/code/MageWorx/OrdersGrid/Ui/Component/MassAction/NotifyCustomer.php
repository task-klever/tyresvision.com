<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\OrdersGrid\Ui\Component\MassAction;

class NotifyCustomer extends OptionsAbstract implements \JsonSerializable
{
    const SEND_EMAIL = 1;

    /**
     * Get options
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        if (empty($this->options)) {
            $this->prepareOptionsData();
            /**
             * Capture
             * Invoice
             * Invoice → Print
             * Ship
             * Ship → Print
             * Invoice → Capture
             * Invoice → Capture → Ship
             * Invoice → Capture → Ship → Print
             */
            $this->getMatchingOptions();

            /**
             * RESEND EMAILS OPTIONS >>>
             *
             * Re-send Order email
             * Re-send Invoice email
             * Re-send Shipment email
             */
            $this->options['resend_order_email'] = array_merge_recursive(
                $this->options['resend_order_email'] = [
                    'type' => 'resend_order_email',
                    'label' => __('Re-send Order Email'),
                    'url' => $this->urlBuilder->getUrl(
                        static::PATH_RESEND_EMAIL,
                        [
                            'order' => 1,
                            'invoice' => 0,
                            'shipment' => 0
                        ]
                    )
                ],
                $this->additionalData
            );

            $this->options['resend_invoice_email'] = array_merge_recursive(
                $this->options['resend_invoice_email'] = [
                    'type' => 'resend_invoice_email',
                    'label' => __('Re-send Invoice Email'),
                    'url' => $this->urlBuilder->getUrl(
                        static::PATH_RESEND_EMAIL,
                        [
                            'order' => 0,
                            'invoice' => 1,
                            'shipment' => 0
                        ]
                    )
                ],
                $this->additionalData
            );

            $this->options['resend_shipment_email'] = array_merge_recursive(
                $this->options['resend_shipment_email'] = [
                    'type' => 'resend_shipment_email',
                    'label' => __('Re-send Shipment Email'),
                    'url' => $this->urlBuilder->getUrl(
                        static::PATH_RESEND_EMAIL,
                        [
                            'order' => 0,
                            'invoice' => 0,
                            'shipment' => 1
                        ]
                    )
                ],
                $this->additionalData
            );

            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * @return string
     */
    protected function getUrlPath(): string
    {
        return 'mageworx_ordersgrid/order_grid/massShipInvoiceCapture';
    }
}
