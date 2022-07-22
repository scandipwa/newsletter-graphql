<?php
/**
 * ScandiPWA - Progressive Web App for Magento
 *
 * Copyright © Scandiweb, Inc. All rights reserved.
 * See LICENSE for license details.
 *
 * @license OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * @package scandipwa/newsletter-graphql
 * @link    https://github.com/scandipwa/newsletter-graphql
 */

declare(strict_types=1);

namespace ScandiPWA\NewsletterGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class ConfirmSubscribingToNewsletter
 * @package ScandiPWA\NewsletterGraphQl\Model\Resolver
 */
class ConfirmSubscribingToNewsletter implements ResolverInterface
{
    /**
     * Newsletter subscription confirmation statuses
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * @var SubscriberFactory
     */
    protected SubscriberFactory $subscriberFactory;

    /**
     * ConfirmSubscribingToNewsletter constructor.
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        SubscriberFactory $subscriberFactory
    ) {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Resolve product review ratings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array[]|Value|mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['id']) || !isset($args['code'])) {
            return [
                'status' => self::STATUS_FAILED,
                'message' => __('Required parameter "id" or "code" is missing.')
            ];
        }

        $id = $args['id'];
        $code = $args['code'];

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create()->load($id);

        if ($subscriber->getId() && $subscriber->getCode()) {
            if ((int)$subscriber->getStatus() === Subscriber::STATUS_SUBSCRIBED) {
                return [
                    'status' => self::STATUS_SUCCESS,
                    'message' => __('Your subscription has already been confirmed.')
                ];
            } else if ($subscriber->confirm($code)) {
                return [
                    'status' => self::STATUS_SUCCESS,
                    'message' => __('Your subscription has been confirmed.')
                ];
            } else {
                return [
                    'status' => self::STATUS_FAILED,
                    'message' => __('This is an invalid subscription confirmation code.')
                ];
            }
        } else {
            return [
                'status' => self::STATUS_FAILED,
                'message' => __('This is an invalid subscription ID.')
            ];
        }

        return [
            'status' => self::STATUS_FAILED,
            'message' => __('Something went wrong!')
        ];
    }
}
