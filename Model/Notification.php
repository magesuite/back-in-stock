<?php
namespace MageSuite\BackInStock\Model;

class Notification extends \Magento\Framework\Model\AbstractModel implements \MageSuite\BackInStock\Api\Data\NotificationInterface
{
    protected function _construct()
    {
        $this->_init(\MageSuite\BackInStock\Model\ResourceModel\Notification::class);
    }

    public function setId($id)
    {
        $this->setData('id', $id);

        return $this;
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function setSubscriptionId($id)
    {
        $this->setData('subscription_id', $id);

        return $this;
    }

    public function getSubscriptionId()
    {
        return $this->getData('subscription_id');
    }

    /**
     * @return string
     */
    public function getNotificationType()
    {
        return $this->getData('notification_type');
    }

    /**
     * @param string $notificationType
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setNotificationType($notificationType)
    {
        $this->setData('notification_type', $notificationType);

        return $this;
    }

    /**
     * @return int
     */
    public function getMessage()
    {
        return $this->getData('message');
    }

    /**
     * @param int $message
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setMessage($message)
    {
        $this->setData('message', $message);

        return $this;
    }
}
