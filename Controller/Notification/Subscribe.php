<?php
namespace MageSuite\BackInStock\Controller\Notification;

class Subscribe extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    /**
     * @var \MageSuite\BackInStock\Service\SubscriptionEntityCreator
     */
    private $subscriptionEntityCreator;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \MageSuite\BackInStock\Service\SubscriptionEntityCreator $subscriptionEntityCreator
    )
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);

        $this->subscriptionEntityCreator = $subscriptionEntityCreator;
    }

    public function execute()
    {
        $params = $this->_request->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setPath($url);

        try {
            $this->subscriptionEntityCreator->subscribe($params);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect;
        }
        return $resultRedirect;
    }
}