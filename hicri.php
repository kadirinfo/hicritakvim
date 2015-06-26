<?php
namespace hicritakvim;

include_once('hilal.php');

class hicri
{


    /**
     * @param $date
     * @param bool $asString
     * @return bool|int
     */
    static public function dayOfWeek($date,$asString=true){
        if (is_string($date))
            $date = parseDateStr($date);

        if (!is_array($date) || !isset($date['day']) || !isset($date['month']) || !isset($date['year']))
            return false; //throw new exception

        $dayOfWeek = intval( date('N', mktime(0,0,0,$date['month'],$date['day'],$date['year'])) );

        return $asString ? hicri::$dayOfWeeks[$dayOfWeek] : $dayOfWeek;
    }
    /**
     * @param $date
     * @param bool $resultType
     * @return array|bool|null|string
     */
    static public function hicriMiladi($date, $resultType = false)
    {

        if (is_string($date))
            $date = parseDateStr($date);

        if (!is_array($date) || !isset($date['day']) || !isset($date['month']) || !isset($date['year']))
            return false; //throw new exception


        //$dateParsed = parseDateStr($date);
        $calculated = self::hicriMiladiOto($date);

        $aybasi = $date;
        $aybasi['day'] = 1;

        $aybasiMiladi = self::hicriMiladiOto($aybasi);

        $hilalAybasi = self::hilalBul($aybasiMiladi);

        if ($hilalAybasi === false) {
            $dayOfWeek = hicri::dayOfWeek($calculated);
            $result = ['date' => $calculated, 'hilal' => false, 'dow'=>$dayOfWeek];
        } else {


            $hilaleGore = mktime(0, 0, 0, $hilalAybasi['month'], $hilalAybasi['day'] + ($date['day'] - 1), $hilalAybasi['year']);
            $sonuc = parseDateStr(date('Y-m-d', $hilaleGore));
            $dayOfWeek = hicri::dayOfWeek($sonuc);
            $result = ['date' => $sonuc, 'hilal' => $hilalAybasi, 'dow'=>$dayOfWeek];
        }

        //validation
        if (hicri::hicriMonthLength($date) < $date['day']) {
            //showError("ayın günü aydan büyük");
            //$result['error'] = "ayın günü aydan büyük";
            $result['error'] = "Geçersiz tarih";
            //todo: yanlış tarih düzeltilebilir mi diye bi bakabiliriz.
            //$fark =$date['day'] - hicri::hicriMonthLength($date);
            //$result['date'] = parseDateStr( date('Y-m-d', mktime(0,0,0,$result['date']['month'],$result['date']['day']+$fark,$result['date']['year'])) );
        }

        //miladi tarihin geçerli bir tarih oldugundan emin ol
        $result['date'] = parseDateStr( date('Y-m-d', mktime(0,0,0,$result['date']['month'],$result['date']['day'],$result['date']['year'])) );

        if ($resultType == 'string')
            return self::miladiStr($result['date']);
        elseif (in_array($resultType, ['date', 'array']))
            return $result['date'];
        else
            return $result;
    }

    /**
     * @param $date
     * @return bool|float
     */
    static public function hicriMonthLength($date)
    {

        if (is_string($date))
            $date = parseDateStr($date);

        if (!is_array($date) || !isset($date['day']) || !isset($date['month']) || !isset($date['year']))
            return false; //throw new exception

        $aybasiHicri = $date;
        $aysonuHicri = $date;
        $aybasiHicri['day'] = 1;

        $aysonuHicri['day'] = 1;
        if (++$aysonuHicri['month'] > 12) {
            $aysonuHicri['month'] = 1;
            $aysonuHicri['year']++;
        }

        $aybasiMiladi = hicri::hicriMiladiOto($aybasiHicri);
        $aysonuMiladi = hicri::hicriMiladiOto($aysonuHicri);

        $aybasiHilal = hicri::hilalBul($aybasiMiladi);
        $aysonuHilal = hicri::hilalBul($aysonuMiladi);

        if ($aybasiHilal === false || $aysonuHilal === false) {
            //hilal olmadan kaç gün olduğu hesaplanamaz, otomatik hesaplat
            $aybasiHilalDate = mktime(0, 0, 0, $aybasiMiladi['month'], $aybasiMiladi['day'], $aybasiMiladi['year']);
            $aysonuHilalDate = mktime(0, 0, 0, $aysonuMiladi['month'], $aysonuMiladi['day'], $aysonuMiladi['year']);
            $result = floor(($aysonuHilalDate - $aybasiHilalDate) / 24 * 60 * 60);
            return $result;

        } else {
            $aybasiHilalDate = mktime(0, 0, 0, $aybasiHilal['month'], $aybasiHilal['day'], $aybasiHilal['year']);
            $aysonuHilalDate = mktime(0, 0, 0, $aysonuHilal['month'], $aysonuHilal['day'], $aysonuHilal['year']);
            $result = floor(($aysonuHilalDate - $aybasiHilalDate) / (24 * 60 * 60));
            return $result;
        }


    }

    /**
     * @param $date
     * @return null|string
     */
    static public function hicriStr($date)
    {
        if (is_string($date))
            $date = parseDateStr($date);

        if (!is_array($date) || !isset($date['day']) || !isset($date['month']) || !isset($date['year']))
            return null; //throw new exception

        return $date['day'] . ' ' . self::$hicriMonth[$date['month']] . ' ' . $date['year'];
    }

    /**
     * @param $date
     * @return null|string
     */
    static public function miladiStr($date)
    {
        if (is_string($date))
            $date = parseDateStr($date);

        if (!is_array($date) || !isset($date['day']) || !isset($date['month']) || !isset($date['year']))
            return null; //throw new exception

        return $date['day'] . ' ' . self::$miladiMonth[$date['month']] . ' ' . $date['year'];
    }

    /**
     * @param $date
     * @param string $resultType
     * @return array|bool|null|string
     */
    static public function miladiHicri($date, $resultType = 'default')
    {

        if (is_string($date))
            $date = parseDateStr($date);

        if (!is_array($date) || !isset($date['day']) || !isset($date['month']) || !isset($date['year']))
            return false; //throw new exception

        $dayOfWeek = hicri::dayOfWeek($date);

        $calculated = self::miladiHicriOto($date);

        $aybasi = $calculated;
        $aybasi['day'] = 1;

        $aybasiMiladi = self::hicriMiladiOto($aybasi);

        $hilalAybasi = self::hilalBul($aybasiMiladi);

        if ($hilalAybasi === false) {
            $hicridate = $calculated;
            $result = ['date' => $calculated, 'hilal' => false];
        } else { //todo
            $hilalMiladiTime = mktime(0, 0, 0, $hilalAybasi['month'], $hilalAybasi['day'], $hilalAybasi['year']);
            $miladiTime = mktime(0, 0, 0, $date['month'], $date['day'], $date['year']);

            //hicri yeni ay mı değil mi

            $hicridate = $calculated;
            $hicridate['day'] = floor(($miladiTime - $hilalMiladiTime) / (60 * 60 * 24) )+ 1;

            $hicriMonthLength = hicri::hicriMonthLength($hicridate);

            if($hicridate['day']<1){
                //önceki ay
                if (--$hicridate['month'] < 1) {
                    $hicridate['month'] = 12;
                    $hicridate['year']--;
                }

                $hicridate['day'] = $hicriMonthLength;
            }

            $result = ['date' => $hicridate, 'hilal' => $hilalAybasi, 'dow'=>$dayOfWeek];

            /*
            if ($hicriMonthLength < $hicridate['day']) {
                //showError("ayın günü aydan büyük");
                $result['error'] = "Geçersiz tarih";
            }*/
        }

        if ($hicridate['day'] >= 27) {

            $yeniAyinHilali = self::hilalBul($hicridate, -1);

            if ($yeniAyinHilali !== false) {
                if ($hicridate['month'] >= 12) {
                    $hicridate['month'] = 1;
                    $hicridate['year']++;
                } else {
                    $hicridate['month']++;
                }
                $hicridate['day'] = $date['day'] - $yeniAyinHilali['day'];

                $result = ['date' => $hicridate, 'hilal' => parseDateStr($yeniAyinHilali), 'dow'=>$dayOfWeek];
            }
        }

        if ($resultType == 'string')
            return self::hicriStr($result['date']);
        elseif (in_array($resultType, ['date', 'array']))
            return $result['date'];
        else
            return $result;
    }

    /**
     * @param $date
     * @param int $scobe
     * @return array|bool
     */
    static public function hilalBul($date, $scobe = 0)
    {
        if (is_string($date))
            $date = parseDateStr($date);

        if (!is_array($date) || !isset($date['day']) || !isset($date['month']) || !isset($date['year']))
            return false; //throw new exception

        if ($scobe == 0) {
            $start = -3;
            $end = +3;
        } elseif ($scobe < 0) {
            $start = -3;
            $end = 0;
        } elseif ($scobe > 0) {
            $start = 0;
            $end = 3;
        } else {
            return false;
        }

        for ($i = $start; $i <= $end; $i++) {
            $newdate = mktime(0, 0, 0, $date['month'], $date['day'] + $i, $date['year']);
            $array[] = date('Y-m-d', $newdate);
        }

        foreach ($array as $val) {
            if (in_array($val, self::$hilals)) {
                return parseDateStr($val);
            }
        }

        return false;
    }

    /**
     * @param $date
     * @param bool $string
     * @return array|string
     */
    static public function hicriMiladiOto($date, $string = false)
    {
        $day = intval($date['day']);
        $month = intval($date['month']);
        $year = intval($date['year']);


        $jd = intPart((11 * $year + 3) / 30) + 354 * $year + 30 * $month - intPart(($month - 1) / 2) + $day + 1948440 - 385;

        if ($jd > 2299160) {
            $l = $jd + 68569;
            $n = intPart((4 * $l) / 146097);
            $l = $l - intPart((146097 * $n + 3) / 4);
            $i = intPart((4000 * ($l + 1)) / 1461001);
            $l = $l - intPart((1461 * $i) / 4) + 31;
            $j = intPart((80 * $l) / 2447);
            $day = $l - intPart((2447 * $j) / 80);
            $l = intPart($j / 11);
            $month = $j + 2 - 12 * $l;
            $year = 100 * ($n - 49) + $i + $l;
        } else {
            $j = $jd + 1402;
            $k = intPart(($j - 1) / 1461);
            $l = $j - 1461 * $k;
            $n = intPart(($l - 1) / 365) - intPart($l / 1461);
            $i = $l - 365 * $n + 30;
            $j = intPart((80 * $i) / 2447);
            $day = $i - intPart((2447 * $j) / 80);
            $i = intPart($j / 11);
            $month = $j + 2 - 12 * $i;
            $year = 4 * $k + $n + $i - 4716;
        }

        $date2 = array();
        $date2['year'] = $year;
        $date2['month'] = $month;
        $date2['day'] = $day;

        if (!$string)
            return $date2;
        else
            return "{$year}-{$month}-{$day}";
    }

    /**
     * @param $date
     * @param bool $string
     * @return array|string
     */
    static public function miladiHicriOto($date, $string = false)
    {
        $day = intval($date['day']);
        $month = intval($date['month']);
        $year = intval($date['year']);

        if (($year > 1582) or (($year == 1582) and ($month > 10)) or (($year == 1582) and ($month == 10) and ($day > 14))) {
            $jd = intPart((1461 * ($year + 4800 + intPart(($month - 14) / 12))) / 4) + intPart((367 * ($month - 2 - 12 * (intPart(($month - 14) / 12)))) / 12) -
                intPart((3 * (intPart(($year + 4900 + intPart(($month - 14) / 12)) / 100))) / 4) + $day - 32075;
        } else {
            $jd = 367 * $year - intPart((7 * ($year + 5001 + intPart(($month - 9) / 7))) / 4) + intPart((275 * $month) / 9) + $day + 1729777;
        }

        $l = $jd - 1948440 + 10632;
        $n = intPart(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (intPart((10985 - $l) / 5316)) * (intPart((50 * $l) / 17719)) + (intPart($l / 5670)) * (intPart((43 * $l) / 15238));
        $l = $l - (intPart((30 - $j) / 15)) * (intPart((17719 * $j) / 50)) - (intPart($j / 16)) * (intPart((15238 * $j) / 43)) + 29;

        $month = intPart((24 * $l) / 709);
        $day = $l - intPart((709 * $month) / 24);
        $year = 30 * $n + $j - 30;

        $date = array();
        $date['year'] = $year;
        $date['month'] = $month;
        $date['day'] = $day;

        $hicriaylar = [1 => "Muharrem",
            2 => "Safer",
            3 => "Rebîü'l-Evvel",
            4 => "Rebîü'l-Âhir",
            5 => "Cemâziye'l-Evvel",
            6 => "Cemâziye'l-Âhir",
            7 => "Recep",
            8 => "Şa'bân",
            9 => "Ramazan",
            10 => "Şevvâl",
            11 => "Zilka'de",
            12 => "Zilhicce"];

        if (!$string)
            return $date;
        else
            return "{$day}-{$hicriaylar[ $month ]}-{$year}";
    }


    static public $hilals = []; //hilal.php ile guncellenir

    static public $hicriMonth = [1 => "Muharrem", 2 => "Safer", 3 => "Rebîü'l-Evvel", 4 => "Rebîü'l-Âhir", 5 => "Cemâziye'l-Evvel", 6 => "Cemâziye'l-Âhir", 7 => "Recep", 8 => "Şa'bân", 9 => "Ramazan", 10 => "Şevvâl", 11 => "Zilka'de", 12 => "Zilhicce"];
    static public $miladiMonth = [1 => "Ocak", 2 => "Şubat", 3 => "Mart", 4 => "Nisan", 5 => "Mayıs", 6 => "Haziran", 7 => "Temmuz", 8 => "Ağustos", 9 => "Eylül", 10 => "Ekim", 11 => "Kasım", 12 => "Aralık"];
    static public $dayOfWeeks = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];
}

function intPart($float)
{
    if ($float < -0.0000001)
        return ceil($float - 0.0000001);
    else
        return floor($float + 0.0000001);
}

function parseDateStr($dateStr)
{
    if (is_string($dateStr) && preg_match('/(\d{4})\-(\d{1,2})\-(\d{1,2})/i', $dateStr, $m)) {
        return [
            'year' => intval($m[1]),
            'month' => intval($m[2]),
            'day' => intval($m[3]),
        ];
    } else {
        return false;
    }
}
