<?php

namespace MageSuite\BackInStock\Api;

interface NotificationRepositoryInterface
{
    /**
     * @param int $id
     * @return \MageSuite\BackInStock\Api\Data\NotificationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param \MageSuite\BackInStock\Api\Data\NotificationInterface $notification
     * @return \MageSuite\BackInStock\Api\Data\NotificationInterface
     */
    public function save(\MageSuite\BackInStock\Api\Data\NotificationInterface $notification);

    /**
     * @param \MageSuite\BackInStock\Api\Data\NotificationInterface $notification
     * @return void
     */
    public function delete(\MageSuite\BackInStock\Api\Data\NotificationInterface $notification);
}
