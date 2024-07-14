<?php
namespace App\ShipShopperLibrary\Enums;


trait AdministrativeAreaValuesByNamesArrayTrait
{
    public static function getValuesByNamesArray()
    {
        return collect(self::cases())->pluck('value', 'name')->toArray();
    }
}
