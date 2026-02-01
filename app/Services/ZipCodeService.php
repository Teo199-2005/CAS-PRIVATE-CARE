<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZipCodeService
{
    /**
     * Special NY ZIP codes that don't follow the standard 10-14 prefix rule
     */
    private const SPECIAL_NY_ZIPS = ['00501', '00544', '06390'];

    /**
     * NY ZIP code validation regex
     * Matches: 00501, 00544, 06390 (special cases) OR 10xxx-14xxx (standard NY range)
     * Optional: -XXXX suffix for ZIP+4 format
     */
    private const NY_ZIP_REGEX = '/^(00501|00544|06390|1[0-4]\d{3})(-\d{4})?$/';

    /**
     * Validate if a ZIP code is a valid New York State ZIP code
     * 
     * Rules:
     * - Must be 5 digits (optionally with -XXXX for ZIP+4)
     * - First two digits must be 10-14 (standard NY range)
     * - OR must be one of the special NY ZIPs: 00501, 00544, 06390
     * 
     * @param string $zipCode The ZIP code to validate
     * @return bool True if valid NY ZIP, false otherwise
     */
    public static function isValidNYZipCode(string $zipCode): bool
    {
        // Remove any whitespace
        $zipCode = trim($zipCode);
        
        // Check against the NY ZIP regex
        return (bool) preg_match(self::NY_ZIP_REGEX, $zipCode);
    }

    /**
     * Get the 5-digit base ZIP code from a ZIP+4 format
     * 
     * @param string $zipCode Full ZIP code (5 or 9 digit format)
     * @return string 5-digit base ZIP code
     */
    public static function getBaseZipCode(string $zipCode): string
    {
        $zipCode = trim($zipCode);
        
        // Extract first 5 digits if it's a ZIP+4 format
        if (preg_match('/^(\d{5})(-\d{4})?$/', $zipCode, $matches)) {
            return $matches[1];
        }
        
        return $zipCode;
    }

    /**
     * Validate ZIP code format (5 digits or ZIP+4)
     * 
     * @param string $zipCode
     * @return bool
     */
    public static function isValidZipFormat(string $zipCode): bool
    {
        return (bool) preg_match('/^\d{5}(-\d{4})?$/', trim($zipCode));
    }

    /**
     * Get validation details for a ZIP code
     * Returns array with format_valid, ny_valid, and message
     * 
     * @param string $zipCode
     * @return array{format_valid: bool, ny_valid: bool, message: string}
     */
    public static function validateZipCode(string $zipCode): array
    {
        $zipCode = trim($zipCode);
        
        // Check format first
        if (!self::isValidZipFormat($zipCode)) {
            return [
                'format_valid' => false,
                'ny_valid' => false,
                'message' => 'ZIP code must be 5 digits (or 5+4 format)'
            ];
        }
        
        // Check if it's a valid NY ZIP
        if (!self::isValidNYZipCode($zipCode)) {
            return [
                'format_valid' => true,
                'ny_valid' => false,
                'message' => 'ZIP code is not a valid New York State ZIP code'
            ];
        }
        
        return [
            'format_valid' => true,
            'ny_valid' => true,
            'message' => 'Valid NY ZIP code'
        ];
    }

    /**
     * Check if ZIP is a special NY ZIP (00501, 00544, 06390)
     * 
     * @param string $zipCode
     * @return bool
     */
    public static function isSpecialNYZip(string $zipCode): bool
    {
        return in_array(self::getBaseZipCode($zipCode), self::SPECIAL_NY_ZIPS, true);
    }

    /**
     * Lookup ZIP code location using external API or fallback
     * 
     * @param string $zipCode
     * @return string|null Location string like "City, NY" or null if not found
     */
    public static function lookupZipCode(string $zipCode): ?string
    {
        // Get the 5-digit base ZIP
        $zipCode = self::getBaseZipCode($zipCode);
        
        // Validate ZIP code format and NY validity
        if (!preg_match('/^\d{5}$/', $zipCode) || !self::isValidNYZipCode($zipCode)) {
            return null;
        }

        // Check cache first (cache for 30 days since ZIP codes don't change often)
        $cacheKey = "zipcode_lookup_{$zipCode}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // OPTIMIZATION: Check static map FIRST for instant response
        $staticMap = self::getStaticZipCodeMap();
        if (isset($staticMap[$zipCode])) {
            $location = $staticMap[$zipCode];
            Cache::put($cacheKey, $location, now()->addDays(30));
            return $location;
        }

        // If not in static map, try external API as fallback
        try {
            // In local development, SSL verification may fail - disable it for local environments
            $httpClient = Http::timeout(2);
            if (app()->environment('local', 'development')) {
                $httpClient = $httpClient->withOptions(['verify' => false]);
            }
            $response = $httpClient->get("https://api.zippopotam.us/us/{$zipCode}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['places']) && count($data['places']) > 0) {
                    $place = $data['places'][0];
                    $city = $place['place name'] ?? '';
                    $state = $place['state abbreviation'] ?? '';
                    
                    // Only return if it's New York
                    if ($state === 'NY') {
                        $location = "{$city}, {$state}";
                        Cache::put($cacheKey, $location, now()->addDays(30));
                        return $location;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug("ZIP code API lookup failed for {$zipCode}: " . $e->getMessage());
        }

        // For valid NY ZIPs not in static map and API failed, return region-based location
        // This ensures valid NY ZIPs always get a location indicator
        $regionLocation = self::getRegionByZipPrefix($zipCode);
        if ($regionLocation) {
            Cache::put($cacheKey, $regionLocation, now()->addDays(30));
            return $regionLocation;
        }

        return null;
    }

    /**
     * Get NY region based on ZIP code prefix
     * Returns a generic location for valid NY ZIP codes based on their prefix range
     * 
     * @param string $zipCode 5-digit ZIP code
     * @return string|null Region name with ", NY" suffix
     */
    private static function getRegionByZipPrefix(string $zipCode): ?string
    {
        if (!self::isValidNYZipCode($zipCode)) {
            return null;
        }

        // Handle special ZIP codes
        if ($zipCode === '00501' || $zipCode === '00544') {
            return 'Holtsville, NY'; // IRS/USPS special
        }
        if ($zipCode === '06390') {
            return 'Fishers Island, NY';
        }

        // Get first 3 digits for region mapping
        $prefix = (int) substr($zipCode, 0, 3);

        // NYC regions
        if ($prefix >= 100 && $prefix <= 102) return 'Manhattan, NY';
        if ($prefix === 103) return 'Staten Island, NY';
        if ($prefix === 104) return 'Bronx, NY';
        if ($prefix >= 105 && $prefix <= 109) return 'Westchester, NY';
        if ($prefix >= 110 && $prefix <= 111) return 'Long Island, NY'; // Queens/Nassau
        if ($prefix === 112) return 'Brooklyn, NY';
        if ($prefix >= 113 && $prefix <= 114) return 'Long Island, NY'; // Queens
        if ($prefix >= 115 && $prefix <= 119) return 'Long Island, NY'; // Nassau/Suffolk

        // Upstate regions
        if ($prefix >= 120 && $prefix <= 129) return 'Capital Region, NY'; // Albany, Hudson Valley
        if ($prefix >= 130 && $prefix <= 139) return 'Central NY'; // Syracuse area
        if ($prefix >= 140 && $prefix <= 149) return 'Western NY'; // Buffalo, Rochester area

        return 'New York, NY';
    }

    /**
     * Get static ZIP code mapping for common NY ZIP codes
     * This is a fallback when API is unavailable
     */
    private static function getStaticZipCodeMap(): array
    {
        return [
            // Manhattan
            '10001' => 'Manhattan, NY', '10002' => 'Manhattan, NY', '10003' => 'Manhattan, NY',
            '10004' => 'Manhattan, NY', '10005' => 'Manhattan, NY', '10006' => 'Manhattan, NY',
            '10007' => 'Manhattan, NY', '10009' => 'Manhattan, NY', '10010' => 'Manhattan, NY',
            '10011' => 'Manhattan, NY', '10012' => 'Manhattan, NY', '10013' => 'Manhattan, NY',
            '10014' => 'Manhattan, NY', '10016' => 'Manhattan, NY', '10017' => 'Manhattan, NY',
            '10018' => 'Manhattan, NY', '10019' => 'Manhattan, NY', '10020' => 'Manhattan, NY',
            '10021' => 'Manhattan, NY', '10022' => 'Manhattan, NY', '10023' => 'Manhattan, NY',
            '10024' => 'Manhattan, NY', '10025' => 'Manhattan, NY', '10026' => 'Manhattan, NY',
            '10027' => 'Manhattan, NY', '10028' => 'Manhattan, NY', '10029' => 'Manhattan, NY',
            '10030' => 'Manhattan, NY', '10031' => 'Manhattan, NY', '10032' => 'Manhattan, NY',
            '10033' => 'Manhattan, NY', '10034' => 'Manhattan, NY', '10035' => 'Manhattan, NY',
            '10036' => 'Manhattan, NY', '10037' => 'Manhattan, NY', '10038' => 'Manhattan, NY',
            '10039' => 'Manhattan, NY', '10040' => 'Manhattan, NY', '10044' => 'Manhattan, NY',
            '10065' => 'Manhattan, NY', '10069' => 'Manhattan, NY', '10075' => 'Manhattan, NY',
            '10128' => 'Manhattan, NY', '10280' => 'Manhattan, NY',
            
            // Brooklyn
            '11201' => 'Brooklyn, NY', '11203' => 'Brooklyn, NY', '11204' => 'Brooklyn, NY',
            '11205' => 'Brooklyn, NY', '11206' => 'Brooklyn, NY', '11207' => 'Brooklyn, NY',
            '11208' => 'Brooklyn, NY', '11209' => 'Brooklyn, NY', '11210' => 'Brooklyn, NY',
            '11211' => 'Brooklyn, NY', '11212' => 'Brooklyn, NY', '11213' => 'Brooklyn, NY',
            '11214' => 'Brooklyn, NY', '11215' => 'Brooklyn, NY', '11216' => 'Brooklyn, NY',
            '11217' => 'Brooklyn, NY', '11218' => 'Brooklyn, NY', '11219' => 'Brooklyn, NY',
            '11220' => 'Brooklyn, NY', '11221' => 'Brooklyn, NY', '11222' => 'Brooklyn, NY',
            '11223' => 'Brooklyn, NY', '11224' => 'Brooklyn, NY', '11225' => 'Brooklyn, NY',
            '11226' => 'Brooklyn, NY', '11228' => 'Brooklyn, NY', '11229' => 'Brooklyn, NY',
            '11230' => 'Brooklyn, NY', '11231' => 'Brooklyn, NY', '11232' => 'Brooklyn, NY',
            '11233' => 'Brooklyn, NY', '11234' => 'Brooklyn, NY', '11235' => 'Brooklyn, NY',
            '11236' => 'Brooklyn, NY', '11237' => 'Brooklyn, NY', '11238' => 'Brooklyn, NY',
            '11239' => 'Brooklyn, NY',
            
            // Queens
            '11101' => 'Long Island City, NY', '11102' => 'Long Island City, NY',
            '11103' => 'Long Island City, NY', '11104' => 'Long Island City, NY',
            '11105' => 'Long Island City, NY', '11106' => 'Long Island City, NY',
            '11109' => 'Long Island City, NY',
            '11354' => 'Flushing, NY', '11355' => 'Flushing, NY', '11356' => 'Flushing, NY',
            '11357' => 'Flushing, NY', '11358' => 'Flushing, NY',
            '11360' => 'Bayside, NY', '11361' => 'Bayside, NY', '11362' => 'Bayside, NY',
            '11363' => 'Bayside, NY', '11364' => 'Bayside, NY',
            '11365' => 'Fresh Meadows, NY', '11366' => 'Fresh Meadows, NY',
            '11367' => 'Fresh Meadows, NY',
            '11368' => 'Corona, NY', '11369' => 'East Elmhurst, NY',
            '11370' => 'Elmhurst, NY', '11371' => 'Elmhurst, NY',
            '11372' => 'Jackson Heights, NY', '11373' => 'Jackson Heights, NY',
            '11374' => 'Rego Park, NY', '11375' => 'Forest Hills, NY',
            '11377' => 'Woodside, NY', '11378' => 'Maspeth, NY',
            '11379' => 'Middle Village, NY', '11385' => 'Ridgewood, NY',
            '11411' => 'Jamaica, NY', '11412' => 'Jamaica, NY', '11413' => 'Jamaica, NY',
            '11414' => 'Jamaica, NY', '11415' => 'Jamaica, NY', '11416' => 'Jamaica, NY',
            '11417' => 'Jamaica, NY', '11418' => 'Jamaica, NY', '11419' => 'Jamaica, NY',
            '11420' => 'Jamaica, NY', '11421' => 'Jamaica, NY', '11422' => 'Jamaica, NY',
            '11423' => 'Jamaica, NY', '11424' => 'Jamaica, NY', '11425' => 'Jamaica, NY',
            '11426' => 'Jamaica, NY', '11427' => 'Jamaica, NY', '11428' => 'Jamaica, NY',
            '11429' => 'Jamaica, NY', '11430' => 'Jamaica, NY', '11432' => 'Jamaica, NY',
            '11433' => 'Jamaica, NY', '11434' => 'Jamaica, NY', '11435' => 'Jamaica, NY',
            '11436' => 'Jamaica, NY',
            
            // Bronx
            '10451' => 'Bronx, NY', '10452' => 'Bronx, NY', '10453' => 'Bronx, NY',
            '10454' => 'Bronx, NY', '10455' => 'Bronx, NY', '10456' => 'Bronx, NY',
            '10457' => 'Bronx, NY', '10458' => 'Bronx, NY', '10459' => 'Bronx, NY',
            '10460' => 'Bronx, NY', '10461' => 'Bronx, NY', '10462' => 'Bronx, NY',
            '10463' => 'Bronx, NY', '10464' => 'Bronx, NY', '10465' => 'Bronx, NY',
            '10466' => 'Bronx, NY', '10467' => 'Bronx, NY', '10468' => 'Bronx, NY',
            '10469' => 'Bronx, NY', '10470' => 'Bronx, NY', '10471' => 'Bronx, NY',
            '10472' => 'Bronx, NY', '10473' => 'Bronx, NY', '10474' => 'Bronx, NY',
            '10475' => 'Bronx, NY',
            
            // Staten Island
            '10301' => 'Staten Island, NY', '10302' => 'Staten Island, NY',
            '10303' => 'Staten Island, NY', '10304' => 'Staten Island, NY',
            '10305' => 'Staten Island, NY', '10306' => 'Staten Island, NY',
            '10307' => 'Staten Island, NY', '10308' => 'Staten Island, NY',
            '10309' => 'Staten Island, NY', '10310' => 'Staten Island, NY',
            '10311' => 'Staten Island, NY', '10312' => 'Staten Island, NY',
            '10314' => 'Staten Island, NY',
            
            // Long Island
            '11501' => 'Hempstead, NY', '11530' => 'Hempstead, NY',
            '11550' => 'Hempstead, NY', '11552' => 'Hempstead, NY',
            '11553' => 'Hempstead, NY', '11554' => 'Hempstead, NY',
            '11555' => 'Hempstead, NY', '11556' => 'Hempstead, NY',
            '11557' => 'Hempstead, NY', '11558' => 'Hempstead, NY',
            '11559' => 'Hempstead, NY', '11560' => 'Hempstead, NY',
            '11561' => 'Hempstead, NY', '11563' => 'Hempstead, NY',
            '11565' => 'Hempstead, NY', '11566' => 'Hempstead, NY',
            '11568' => 'Hempstead, NY', '11569' => 'Hempstead, NY',
            '11570' => 'Hempstead, NY', '11571' => 'Hempstead, NY',
            '11572' => 'Hempstead, NY', '11575' => 'Hempstead, NY',
            '11576' => 'Hempstead, NY', '11577' => 'Hempstead, NY',
            '11579' => 'Hempstead, NY', '11580' => 'Hempstead, NY',
            '11581' => 'Hempstead, NY', '11582' => 'Hempstead, NY',
            '11590' => 'Hempstead, NY', '11596' => 'Hempstead, NY',
            '11598' => 'Hempstead, NY', '11599' => 'Hempstead, NY',
            
            // Westchester
            '10501' => 'White Plains, NY', '10502' => 'White Plains, NY',
            '10504' => 'White Plains, NY', '10505' => 'White Plains, NY',
            '10506' => 'White Plains, NY', '10507' => 'White Plains, NY',
            '10510' => 'White Plains, NY', '10514' => 'White Plains, NY',
            '10520' => 'White Plains, NY', '10522' => 'White Plains, NY',
            '10523' => 'White Plains, NY', '10524' => 'White Plains, NY',
            '10526' => 'White Plains, NY', '10527' => 'White Plains, NY',
            '10528' => 'White Plains, NY', '10530' => 'White Plains, NY',
            '10532' => 'White Plains, NY', '10533' => 'White Plains, NY',
            '10538' => 'White Plains, NY', '10543' => 'White Plains, NY',
            '10546' => 'White Plains, NY', '10547' => 'White Plains, NY',
            '10548' => 'White Plains, NY', '10549' => 'White Plains, NY',
            '10550' => 'White Plains, NY', '10552' => 'White Plains, NY',
            '10553' => 'White Plains, NY', '10560' => 'White Plains, NY',
            '10562' => 'White Plains, NY', '10566' => 'White Plains, NY',
            '10567' => 'White Plains, NY', '10570' => 'White Plains, NY',
            '10573' => 'White Plains, NY', '10576' => 'White Plains, NY',
            '10577' => 'White Plains, NY', '10578' => 'White Plains, NY',
            '10579' => 'White Plains, NY', '10580' => 'White Plains, NY',
            '10583' => 'White Plains, NY', '10587' => 'White Plains, NY',
            '10588' => 'White Plains, NY', '10589' => 'White Plains, NY',
            '10590' => 'White Plains, NY', '10591' => 'White Plains, NY',
            '10594' => 'White Plains, NY', '10595' => 'White Plains, NY',
            '10596' => 'White Plains, NY', '10597' => 'White Plains, NY',
            '10598' => 'White Plains, NY',
            '10601' => 'White Plains, NY', '10602' => 'White Plains, NY',
            '10603' => 'White Plains, NY', '10604' => 'White Plains, NY',
            '10605' => 'White Plains, NY', '10606' => 'White Plains, NY',
            '10607' => 'White Plains, NY',
            '10701' => 'Yonkers, NY', '10702' => 'Yonkers, NY',
            '10703' => 'Yonkers, NY', '10704' => 'Yonkers, NY',
            '10705' => 'Yonkers, NY', '10706' => 'Yonkers, NY',
            '10707' => 'Yonkers, NY', '10708' => 'Yonkers, NY',
            '10709' => 'Yonkers, NY', '10710' => 'Yonkers, NY',
            
            // Albany area
            '12201' => 'Albany, NY', '12202' => 'Albany, NY', '12203' => 'Albany, NY',
            '12204' => 'Albany, NY', '12205' => 'Albany, NY', '12206' => 'Albany, NY',
            '12207' => 'Albany, NY', '12208' => 'Albany, NY', '12209' => 'Albany, NY',
            '12210' => 'Albany, NY', '12211' => 'Albany, NY', '12212' => 'Albany, NY',
            
            // Buffalo area
            '14201' => 'Buffalo, NY', '14202' => 'Buffalo, NY', '14203' => 'Buffalo, NY',
            '14204' => 'Buffalo, NY', '14205' => 'Buffalo, NY', '14206' => 'Buffalo, NY',
            '14207' => 'Buffalo, NY', '14208' => 'Buffalo, NY', '14209' => 'Buffalo, NY',
            '14210' => 'Buffalo, NY', '14211' => 'Buffalo, NY', '14212' => 'Buffalo, NY',
            '14213' => 'Buffalo, NY', '14214' => 'Buffalo, NY', '14215' => 'Buffalo, NY',
            '14216' => 'Buffalo, NY', '14217' => 'Buffalo, NY', '14218' => 'Buffalo, NY',
            '14219' => 'Buffalo, NY', '14220' => 'Buffalo, NY', '14221' => 'Buffalo, NY',
            '14222' => 'Buffalo, NY', '14223' => 'Buffalo, NY', '14224' => 'Buffalo, NY',
            '14225' => 'Buffalo, NY', '14226' => 'Buffalo, NY', '14227' => 'Buffalo, NY',
            '14228' => 'Buffalo, NY',
            
            // Rochester area
            '14601' => 'Rochester, NY', '14602' => 'Rochester, NY', '14603' => 'Rochester, NY',
            '14604' => 'Rochester, NY', '14605' => 'Rochester, NY', '14606' => 'Rochester, NY',
            '14607' => 'Rochester, NY', '14608' => 'Rochester, NY', '14609' => 'Rochester, NY',
            '14610' => 'Rochester, NY', '14611' => 'Rochester, NY', '14612' => 'Rochester, NY',
            '14613' => 'Rochester, NY', '14614' => 'Rochester, NY', '14615' => 'Rochester, NY',
            '14616' => 'Rochester, NY', '14617' => 'Rochester, NY', '14618' => 'Rochester, NY',
            '14619' => 'Rochester, NY', '14620' => 'Rochester, NY', '14621' => 'Rochester, NY',
            '14622' => 'Rochester, NY', '14623' => 'Rochester, NY', '14624' => 'Rochester, NY',
            '14625' => 'Rochester, NY', '14626' => 'Rochester, NY', '14627' => 'Rochester, NY',
            
            // Syracuse area
            '13201' => 'Syracuse, NY', '13202' => 'Syracuse, NY', '13203' => 'Syracuse, NY',
            '13204' => 'Syracuse, NY', '13205' => 'Syracuse, NY', '13206' => 'Syracuse, NY',
            '13207' => 'Syracuse, NY', '13208' => 'Syracuse, NY', '13209' => 'Syracuse, NY',
            '13210' => 'Syracuse, NY', '13211' => 'Syracuse, NY', '13212' => 'Syracuse, NY',
            '13213' => 'Syracuse, NY', '13214' => 'Syracuse, NY', '13215' => 'Syracuse, NY',
            '13217' => 'Syracuse, NY', '13218' => 'Syracuse, NY', '13219' => 'Syracuse, NY',
            '13220' => 'Syracuse, NY', '13221' => 'Syracuse, NY', '13224' => 'Syracuse, NY',
            
            // Binghamton area
            '13901' => 'Binghamton, NY', '13902' => 'Binghamton, NY', '13903' => 'Binghamton, NY',
            '13904' => 'Binghamton, NY', '13905' => 'Binghamton, NY',
            
            // Utica area
            '13501' => 'Utica, NY', '13502' => 'Utica, NY', '13503' => 'Utica, NY',
            '13504' => 'Utica, NY', '13505' => 'Utica, NY',
            
            // Ithaca
            '14850' => 'Ithaca, NY', '14851' => 'Ithaca, NY', '14852' => 'Ithaca, NY',
            '14853' => 'Ithaca, NY',
        ];
    }
}

