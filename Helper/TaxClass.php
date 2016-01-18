<?php

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class TaxClass
 */
class TaxClass
{
    /**
     * Avatax shipping tax code
     */
    const SHIPPING_LINE_AVATAX_TAX_CODE = 'FR020100';

    /**
     * Gift wrapping tax class
     *
     * Copied from \Magento\GiftWrapping\Helper\Data since it is an Enterprise-only module
     */
    const XML_PATH_TAX_CLASS_GIFT_WRAPPING = 'tax/classes/wrapping_tax_class';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig = null;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * Class constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->taxClassRepository = $taxClassRepository;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * Get the AvaTax Tax Code for a product
     *
     * @param int $taxClassId
     * @return string|null
     */
    protected function getAvaTaxTaxCode($taxClassId)
    {
        try {
            $taxClass = $this->taxClassRepository->get($taxClassId);
            return $taxClass->getAvataxCode();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get AvaTax Tax Code for a product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return null|string
     */
    public function getAvataxTaxCodeForProduct(\Magento\Catalog\Model\Product $product)
    {
        return $this->getAvaTaxTaxCode($product->getTaxClassId());
    }

    /**
     * Get AvaTax Tax Code for shipping
     *
     * @return string
     */
    public function getAvataxTaxCodeForShipping()
    {
        return self::SHIPPING_LINE_AVATAX_TAX_CODE;
    }

    /**
     * @param $store
     * @return null|string
     */
    public function getAvataxTaxCodeForGiftOptions($store)
    {
        $taxClassId = $this->scopeConfig->getValue(
            self::XML_PATH_TAX_CLASS_GIFT_WRAPPING,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return $this->getAvaTaxTaxCode($taxClassId);
    }

    /**
     * Get AvaTax Customer Usage Type for customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return null|string
     */
    public function getAvataxTaxCodeForCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customerGroupId = $customer->getGroupId();
        if (!$customerGroupId) {
            return null;
        }

        try {
            $customerGroup = $this->customerGroupRepository->getById($customerGroupId);
            $taxClassId = $customerGroup->getTaxClassId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
        return $this->getAvaTaxTaxCode($taxClassId);
    }
}
