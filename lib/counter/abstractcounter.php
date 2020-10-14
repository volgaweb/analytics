<?php
namespace VW\Analytics\Counter;

interface abstractCounter
{
    public function getPrefetch(): array;
    public function getHeaderCounter(): ?string;
    public function getHeadCounter(): ?string;
    public function getFooterCounter(): ?string;

    public function getHeaderCounterLazy(): ?string;
    public function getHeadCounterLazy(): ?string;
    public function getFooterCounterLazy(): ?string;
}