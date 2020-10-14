<?php

namespace VW\Analytics\Counter;

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use VW\Main\Meta\DNSPrefetch;

class baseCounter implements abstractCounter
{
    protected $counterString;
    protected $preconnectDomains = [];

    public function __construct(string $counterString)
    {
        if (empty($counterString)) {
            throw new \Exception('Counter identifier is not specified for class ' . __CLASS__);
        } else {
            $this->counterString = $counterString;
        }
    }



    public function getPrefetch(): array
    {
        return $this->preconnectDomains;
    }

    /**
     * базово возвращаем базовые варианты
     * @return string|null
     */
    public function getHeaderCounterLazy(): ?string
    {
        return $this->getHeaderCounter();
    }
    /**
     * базово возвращаем базовые варианты
     * @return string|null
     */
    public function getHeadCounterLazy(): ?string
    {
        return $this->getHeaderCounter();
    }
    /**
     * базово возвращаем базовые варианты
     * @return string|null
     */
    public function getFooterCounterLazy(): ?string
    {
        return $this->getHeaderCounter();
    }

    public function getHeaderCounter(): ?string
    {
       return null;
    }

    public function getHeadCounter(): ?string
    {
        return null;
    }

    public function getFooterCounter(): ?string
    {
        return null;
    }
}