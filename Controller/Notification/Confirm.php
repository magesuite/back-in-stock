<?php
namespace MageSuite\BackInStock\Controller\Notification;

class Confirm extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;
    /**
     * @var \MageSuite\BackInStock\Service\ConfirmationUpdater
     */
    protected $confirmationUpdater;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \MageSuite\BackInStock\Service\ConfirmationUpdater $confirmationUpdater,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository
    )
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);

        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->confirmationUpdater = $confirmationUpdater;
    }

    public function execute()
    {
        $params = $this->_request->getParams();

        try {
            $this->confirmationUpdater->update($params);
        } catch (\Exception $e) {
            return $this->_redirect('/');
        }
        return $this->_redirect('/');
    }
}