<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types = 1);

namespace Element119\CmsDuplicator\Controller\Adminhtml\Cms;

use Element119\CmsDuplicator\Model\Duplicator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class MassDuplicate extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Magento_Cms::save';

    /** @var Duplicator */
    private Duplicator $cmsDuplicator;

    /**
     * @param Context $context
     * @param Duplicator $cmsDuplicator
     */
    public function __construct(
        Context $context,
        Duplicator $cmsDuplicator
    ) {
        parent::__construct($context);

        $this->cmsDuplicator = $cmsDuplicator;
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $cmsEntityType = explode('_', $this->getRequest()->getParam('namespace'))[1];
        $selectedCmsEntities = $this->getRequest()->getParam('selected');

        try {
            foreach ($selectedCmsEntities as $cmsEntityId) {
                $this->cmsDuplicator->duplicateCmsEntity($cmsEntityId, $cmsEntityType);
            }

            $this->messageManager->addSuccessMessage(__(
                'Successfully duplicated %1 %2(s).',
                count($selectedCmsEntities),
                $cmsEntityType
            ));

            return $resultRedirect->setPath("cms/{$cmsEntityType}/");
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while trying to duplicate selected item(s).')
            );

            return $resultRedirect->setPath('*/*/*');
        }
    }
}
