<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Peter Meester <p.meester@bluem.nl>
 */

namespace Bluem\Integration\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Example data source
 */
class Cart extends \Magento\Checkout\CustomerData\Cart implements SectionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        /**
         * TODO; Check if age verification is turned on.
         */
        $totals = $this->getQuote()->getTotals();
        $subtotalAmount = $totals['subtotal']->getValue();
        return [
            'age_verification_enabled' => 'yes',
            'summary_count' => $this->getSummaryCount(),
            'subtotalAmount' => $subtotalAmount,
            'subtotal' => isset($totals['subtotal'])
                ? $this->checkoutHelper->formatPrice($subtotalAmount)
                : 0,
            'possible_onepage_checkout' => $this->isPossibleOnepageCheckout(),
            'items' => $this->getRecentItems(),
            'extra_actions' => $this->layout->createBlock(\Magento\Catalog\Block\ShortcutButtons::class)->toHtml(),
            'isGuestCheckoutAllowed' => $this->isGuestCheckoutAllowed(),
            'website_id' => $this->getQuote()->getStore()->getWebsiteId(),
            'storeId' => $this->getQuote()->getStore()->getStoreId()
        ];
    }
    
    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int|float
     */
    protected function getSummaryCount()
    {
        if (!$this->summeryCount) {
            $this->summeryCount = $this->checkoutCart->getSummaryQty() ?: 0;
        }
        return $this->summeryCount;
    }

    /**
     * Check if one page checkout is available
     *
     * @return bool
     */
    protected function isPossibleOnepageCheckout()
    {
        return $this->checkoutHelper->canOnepageCheckout() && !$this->getQuote()->getHasError();
    }

    /**
     * Get array of last added items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $productId = $item->getProduct()->getId();
                $products = $this->catalogUrl->getRewriteByProductStore([$productId => $item->getStoreId()]);
                if (!isset($products[$productId])) {
                    continue;
                }
                $urlDataObject = new \Magento\Framework\DataObject($products[$productId]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }
            $items[] = $this->itemPoolInterface->getItemData($item);
        }
        return $items;
    }

    /**
     * Return customer quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getAllQuoteItems()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote()->getAllItems();
        }
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Check if guest checkout is allowed
     *
     * @return bool
     */
    public function isGuestCheckoutAllowed()
    {
        return $this->checkoutHelper->isAllowedGuestCheckout($this->checkoutSession->getQuote());
    }
}