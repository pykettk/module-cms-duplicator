<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\CmsDuplicator\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;

class Duplicator
{
    public const CMS_DUPLICATE_URL = 'cms_duplicator/cms/duplicate';

    private const CMS_ENTITY_TYPES = [
        'block',
        'page',
    ];

    /** @var BlockRepositoryInterface */
    private BlockRepositoryInterface $blockRepository;

    /** @var PageRepositoryInterface */
    private PageRepositoryInterface $pageRepository;

    /** @var Escaper */
    private Escaper $escaper;

    /** @var UrlInterface */
    private UrlInterface $urlBuilder;

    /**
     * @param BlockRepositoryInterface $blockRepository
     * @param PageRepositoryInterface $pageRepository
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        PageRepositoryInterface $pageRepository,
        Escaper $escaper,
        UrlInterface $urlBuilder
    ) {
        $this->blockRepository = $blockRepository;
        $this->pageRepository = $pageRepository;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Duplicate a given CMS entity type with the given ID.
     *
     * @param string $entityId
     * @param string $entityType
     * @return void
     * @throws InputException
     * @throws LocalizedException
     */
    public function duplicateCmsEntity(
        string $entityId,
        string $entityType
    ): void {
        if (!in_array($entityType, self::CMS_ENTITY_TYPES)) {
            throw new InputException(__('Invalid CMS type specified'));
        }

        /** @var BlockRepositoryInterface|PageRepositoryInterface $entityRepository */
        $entityRepository = $this->{"{$entityType}Repository"};

        /** @var BlockInterface|PageInterface $entityModel */
        $entityModel = $entityRepository->getById($entityId);

        $entityModel->setId(null);
        $entityModel->setIdentifier($entityModel->getIdentifier() . '_' . uniqid());
        $entityModel->setIsActive(false);

        $entityRepository->save($entityModel);
    }

    /**
     * Add the duplicate action to the given set of actions for a given CMS entity type.
     *
     * @param array $actions
     * @param string $entityType
     * @return array
     * @throws InputException
     */
    public function addDuplicateAction(
        array $actions,
        string $entityType
    ): array {
        if (!in_array($entityType, self::CMS_ENTITY_TYPES)) {
            throw new InputException(__('Invalid CMS type specified'));
        }

        if (isset($actions['data']['items'])) {
            foreach ($actions['data']['items'] as & $item) {
                if (isset($item['identifier'])) {
                    $title = $this->escaper->escapeHtml($item['title']);

                    $item['actions']['duplicate'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::CMS_DUPLICATE_URL,
                            [
                                'type' => $entityType,
                                'id' => $item["{$entityType}_id"],
                            ]
                        ),
                        'label' => __('Duplicate'),
                        'confirm' => [
                            'title' => __('Duplicate %1', $title),
                            'message' => __('Are you sure you want to duplicate a %1 record?', $title),
                        ],
                        'post' => true,
                    ];
                }
            }
        }

        return $actions;
    }
}
