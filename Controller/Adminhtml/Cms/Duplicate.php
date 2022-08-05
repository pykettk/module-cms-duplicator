<?php
declare(strict_types = 1);

/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
namespace Element119\CmsDuplicator\Controller\Adminhtml\Cms;

use Element119\CmsDuplicator\Model\Duplicator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

class Duplicate extends Action implements ActionInterface
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
        $cmsEntityId = $this->getRequest()->getParam('id');
        $cmsEntityType = $this->getRequest()->getParam('type');
        $editEntity = $this->getRequest()->getParam('edit');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($cmsEntityId && $cmsEntityType) {
            try {
                $newCmsEntity = $this->cmsDuplicator->duplicateCmsEntity($cmsEntityId, $cmsEntityType);
                $this->messageManager->addSuccessMessage(__('You duplicated the %1.', $cmsEntityType));

                return $resultRedirect->setPath(
                    "cms/{$cmsEntityType}/edit",
                    $editEntity ? ["{$cmsEntityType}_id" => $newCmsEntity->getId()] : []
                );
            } catch (InputException | LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath("cms/{$cmsEntityType}/");;
            }
        }

        return $resultRedirect->setPath("cms/{$cmsEntityType}/");
    }
}
