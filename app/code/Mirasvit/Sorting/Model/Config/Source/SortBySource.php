<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.3.20
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

class SortBySource implements ArrayInterface
{
    private $repository;

    public function __construct(
        RankingFactorRepository $repository
    ) {
        $this->repository = $repository;
    }

    public function toOptionArray()
    {
        $result = [
            [
                'label' => 'Attribute',
                'value' => CriterionInterface::CONDITION_SORT_BY_ATTRIBUTE,
            ],
            [
                'label' => 'Ranking Factor',
                'value' => CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR,
            ],
        ];

        return $result;
    }
}
