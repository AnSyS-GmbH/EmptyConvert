<?php

namespace AnSyS\EmptyConvert\Plugin;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Reflection\TypeProcessor;

class ServiceInputProcessorPlugin
{
    /**
     * @var Request
     */
    protected $typeProcessor;

    public function __construct(TypeProcessor $typeProcessor) {
        $this->typeProcessor = $typeProcessor;
    }


    /**
     * Don't convert value if it is empty, because that way you are not able to empty values like prices.
     *
     * @param ServiceInputProcessor $subject
     * @param callable $proceed
     * @param $data
     * @param $type
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvertValue(ServiceInputProcessor $subject,callable $proceed, $data, $type)
    {
        if ($this->typeProcessor->isTypeSimple($type) && $data === "ansys_empty") {
            return "";
        }
        return $proceed($data, $type);
    }
}
