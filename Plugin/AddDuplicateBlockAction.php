<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\CmsDuplicator\Plugin;

use Element119\CmsDuplicator\Model\Duplicator;
use Magento\Cms\Ui\Component\Listing\Column\BlockActions;
use Magento\Framework\Exception\InputException;

class AddDuplicateBlockAction
{
    /** @var Duplicator */
    private Duplicator $cmsDuplicator;

    /**
     * @param Duplicator $cmsDuplicator
     */
    public function __construct(
        Duplicator $cmsDuplicator
    ) {
        $this->cmsDuplicator = $cmsDuplicator;
    }

    /**
     * Add the duplicate action to the CMS block grid's action column.
     *
     * @param BlockActions $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareDataSource(
        BlockActions $subject,
        array $result
    ): array {
        try {
            return $this->cmsDuplicator->addDuplicateAction($result, 'block');
        } catch (InputException $e) {
            return $result;
        }
    }
}
