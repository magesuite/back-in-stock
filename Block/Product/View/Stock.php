<?php

namespace MageSuite\BackInStock\Block\Product\View;

class Stock extends \Magento\ProductAlert\Block\Product\View
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\ProductAlert\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Helper\PostHelper $coreHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    ) {
        parent::__construct($context, $helper, $registry, $coreHelper);
        $this->urlBuilder = $urlBuilder;
        $this->configuration = $configuration;
    }

    public function setTemplate($template)
    {
        if (!$this->configuration->canDisplaySubscriptionForm($this->_storeManager->getStore()->getId())) {
            $template = '';
        } else {
            $this->setSignupUrl($this->_helper->getSaveUrl('stock'));
        }

        return parent::setTemplate($template);
    }

    public function getActionUrl()
    {
        return $this->urlBuilder->getUrl('backinstock/notification/subscribe');
    }
}
