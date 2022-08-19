<?php

namespace MageSuite\BackInStock\Block\Adminhtml;

class SubscriptionsTab extends \Magento\Ui\Component\Layout\Tabs\TabWrapper implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Back In Stock Subscriptions');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('backinstock/customer/subscriptions', ['_current' => true]);
    }
}
