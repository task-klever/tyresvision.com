<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

class OutputChain implements OutputChainInterface
{
    /**
     * @var AmpRequestChecker
     */
    private $ampRequestChecker;

    /**
     * @var OutputProcessorInterface[]
     */
    private $ampProcessors;

    /**
     * @var OutputProcessorInterface[]
     */
    private $processors;

    public function __construct(
        AmpRequestChecker $ampRequestChecker,
        $ampProcessors,
        $processors
    ) {
        $this->ampRequestChecker = $ampRequestChecker;
        $this->ampProcessors = $ampProcessors;
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        $result = true;
        foreach ($this->getPageProcessors() as $processor) {
            if (!$processor->process($output)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    public function getPageProcessors()
    {
        return $this->ampRequestChecker->check() ? $this->ampProcessors : $this->processors;
    }
}
