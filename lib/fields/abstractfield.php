<?php
namespace VW\Analytics\Fields;

interface abstractField
{
    public static function getData();
    public static function handleMailValue(string $value): string;
}