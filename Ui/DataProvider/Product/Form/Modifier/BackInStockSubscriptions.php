<?php
namespace MageSuite\BackInStock\Ui\DataProvider\Product\Form\Modifier;

class BackInStockSubscriptions extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    const DATA_SCOPE       = 'data';
    const DATA_SCOPE_BACK_IN_STOCK = 'back_in_stock';
    const DATA_NOTIFICATION_MODAL = 'back_in_stock_notification_modal';
    /**
     * @var string
     */
    protected static $previousGroup = 'related';

    /**
     * @var int
     */
    protected static $sortOrder = 110;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    private $configuration;


    public function __construct(
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $url,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->request = $request;
        $this->url = $url;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->canShowTab()) {
            return $meta;
        }

        $meta = array_replace_recursive(
            $meta,
            [
                'backinstock' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'additionalClasses' => 'admin__fieldset-section',
                                'label' => __('Back In Stock'),
                                'collapsible' => true,
                                'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'sortOrder' =>
                                    $this->getNextGroupSortOrder(
                                        $meta,
                                        self::$previousGroup,
                                        self::$sortOrder
                                    ),
                            ],
                        ],
                    ],
                    'children' => [
                        static::DATA_NOTIFICATION_MODAL => $this->getNotifyAllButton(),
                        static::DATA_SCOPE_BACK_IN_STOCK => $this->getBackInStockFieldset()
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * @return bool
     */
    protected function canShowTab()
    {
        return $this->configuration->isModuleEnabled();
    }

    protected function getBackInStockFieldset()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Back In Stock'),
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/html',
                        'additionalClasses' => 'admin__fieldset-note',
                        'content' =>
                            $this->layoutFactory->create()->createBlock(
                                \MageSuite\BackInStock\Block\Adminhtml\Product\Edit\Tab\Alerts\BackInStockSubscriptions::class
                            )->toHtml(),
                    ]
                ]
            ]
        ];
    }


    protected function getNotifyAllButton()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Back In Stock'),
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/html',
                        'additionalClasses' => 'admin__fieldset-note',
                        'content' =>
                            '<button type="button" data-bind="css: buttonClasses" class="action-basic" onclick="javascript: openNotifyManuallyModal('. $this->getProductId() .', '. sprintf("'%s'", $this->getFormUrl()) .')">
<span data-bind="text: title">Notify Manually</span>
</button>'
                    ]
                ]
            ]
        ];
    }

    protected function getProductId()
    {
        $params = $this->request->getParams();

        return $params['id'];
    }

    protected function getStoreId()
    {
        $params = $this->request->getParams();

        return isset($params['store']) ? $params['store'] : null;
    }

    protected function getFormUrl()
    {
        return $this->url->getUrl('backinstock/notification/form', ['product_id' => $this->getProductId(), 'store' => $this->getStoreId()]);
    }

}
