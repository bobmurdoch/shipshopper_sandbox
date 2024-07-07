<?php
namespace App\Enums;

enum UsaStatesEnum: string implements AdministrativeAreaWithCodeForShippingApis
{
    case AL = 'Alabama';
    case AK = 'Alaska';
    case AS = 'American Samoa';
    case AZ = 'Arizona';
    case AR = 'Arkansas';
    case CA = 'California';
    case CO = 'Colorado';
    case CT = 'Connecticut';
    case DE = 'Delaware';
    case DC = 'District Of Columbia';
    case FM = 'Federated States Of Micronesia';
    case FL = 'Florida';
    case GA = 'Georgia';
    case GU = 'Guam Gu';
    case HI = 'Hawaii';
    case ID = 'Idaho';
    case IL = 'Illinois';
    case IN = 'Indiana';
    case IA = 'Iowa';
    case KS = 'Kansas';
    case KY = 'Kentucky';
    case LA = 'Louisiana';
    case ME = 'Maine';
    case MH = 'Marshall Islands';
    case MD = 'Maryland';
    case MA = 'Massachusetts';
    case MI = 'Michigan';
    case MN = 'Minnesota';
    case MS = 'Mississippi';
    case MO = 'Missouri';
    case MT = 'Montana';
    case NE = 'Nebraska';
    case NV = 'Nevada';
    case NH = 'New Hampshire';
    case NJ = 'New Jersey';
    case NM = 'New Mexico';
    case NY = 'New York';
    case NC = 'North Carolina';
    case ND = 'North Dakota';
    case MP = 'Northern Mariana Islands';
    case OH = 'Ohio';
    case OK = 'Oklahoma';
    case OR = 'Oregon';
    case PW = 'Palau';
    case PA = 'Pennsylvania';
    case PR = 'Puerto Rico';
    case RI = 'Rhode Island';
    case SC = 'South Carolina';
    case SD = 'South Dakota';
    case TN = 'Tennessee';
    case TX = 'Texas';
    case UT = 'Utah';
    case VT = 'Vermont';
    case VI = 'Virgin Islands';
    case VA = 'Virginia';
    case WA = 'Washington';
    case WV = 'West Virginia';
    case WI = 'Wisconsin';
    case WY = 'Wyoming';
    case AE = 'Armed Forces Africa \ Canada \ Europe \ Middle East';
    case AA = 'Armed Forces America (except Canada)';
    case AP = 'Armed Forces Pacific';
    public function getCodeForShippingApi(ShippingCarrierEnum $shippingCarrier): string
    {
        /*
         * For now these carriers all use the same format for the 'state/province' but
         * add a method here for future carriers that have a different format.
         */
        return match ($shippingCarrier) {
            ShippingCarrierEnum::UPS, ShippingCarrierEnum::FEDEX, ShippingCarrierEnum::USPS => $this->name,
        };
    }
}
