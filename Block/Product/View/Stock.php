<?php
namespace MageSuite\BackInStock\Block\Product\View;

class Stock extends \Magento\ProductAlert\Block\Product\View
{
    const PRODUCT_TYPE_SIMPLE = 'simple';

    protected $customer = null;
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

    /**
     * Prepare stock info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {

        if (!$this->configuration->canDisplaySubscriptionForm($this->getProduct(), $this->_storeManager->getStore()->getId())) {
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

    public function getProductId()
    {
        if ($product = $this->getProduct()) {
            return $product->getId();
        }

        return null;
    }

    public function canRenderForm()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() == self::PRODUCT_TYPE_SIMPLE && $product->isSalable()) {
            return false;
        }

        return true;
    }
}
