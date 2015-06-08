<?php
namespace hicritakvim;

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


    static public $hilals = ['1900-05-01', '1900-05-31', '1900-06-29', '1900-07-29', '1900-08-27', '1900-09-26', '1900-10-25', '1900-11-24', '1900-12-23', '1901-01-22', '1901-02-20', '1901-03-22', '1901-04-20', '1901-05-20', '1901-06-18', '1901-07-18', '1901-08-16', '1901-09-15', '1901-10-14', '1901-11-13', '1901-12-12', '1902-01-11', '1902-02-09', '1902-03-11', '1902-04-10', '1902-05-10', '1902-06-08', '1902-07-08', '1902-08-06', '1902-09-05', '1902-10-04', '1902-11-03', '1902-12-02', '1903-01-01', '1903-01-30', '1903-03-01', '1903-03-30', '1903-04-29', '1903-05-28', '1903-06-27', '1903-07-26', '1903-08-25', '1903-09-23', '1903-10-23', '1903-11-21', '1903-12-21', '1904-01-19', '1904-02-18', '1904-03-18', '1904-04-17', '1904-05-16', '1904-06-15', '1904-07-14', '1904-08-13', '1904-09-11', '1904-10-11', '1904-11-09', '1904-12-09', '1905-01-07', '1905-02-06', '1905-03-08', '1905-04-07', '1905-05-06', '1905-06-05', '1905-07-04', '1905-08-03', '1905-09-01', '1905-10-01', '1905-10-30', '1905-11-29', '1905-12-28', '1906-01-27', '1906-02-25', '1906-03-27', '1906-04-25', '1906-05-25', '1906-06-23', '1906-07-23', '1906-08-21', '1906-09-20', '1906-10-19', '1906-11-18', '1906-12-17', '1907-01-16', '1907-02-14', '1907-03-16', '1907-04-14', '1907-05-14', '1907-06-12', '1907-07-12', '1907-08-10', '1907-09-09', '1907-10-08', '1907-11-07', '1907-12-06', '1908-01-05', '1908-02-04', '1908-03-05', '1908-04-03', '1908-05-03', '1908-06-01', '1908-07-01', '1908-07-30', '1908-08-29', '1908-09-27', '1908-10-27', '1908-11-25', '1908-12-25', '1909-01-23', '1909-02-22', '1909-03-23', '1909-04-22', '1909-05-21', '1909-06-20', '1909-07-19', '1909-08-18', '1909-09-16', '1909-10-16', '1909-11-14', '1909-12-14', '1910-01-12', '1910-02-11', '1910-03-12', '1910-04-11', '1910-05-10', '1910-06-09', '1910-07-08', '1910-08-07', '1910-09-05', '1910-10-05', '1910-11-03', '1910-12-03', '1911-01-02', '1911-02-01', '1911-03-02', '1911-04-01', '1911-04-30', '1911-05-30', '1911-06-28', '1911-07-28', '1911-08-26', '1911-09-25', '1911-10-24', '1911-11-23', '1911-12-22', '1912-01-21', '1912-02-19', '1912-03-20', '1912-04-18', '1912-05-18', '1912-06-16', '1912-07-16', '1912-08-14', '1912-09-13', '1912-10-12', '1912-11-11', '1912-12-10', '1913-01-09', '1913-02-07', '1913-03-09', '1913-04-07', '1913-05-07', '1913-06-05', '1913-07-05', '1913-08-03', '1913-09-02', '1913-10-01', '1913-10-31', '1913-11-30', '1913-12-30', '1914-01-28', '1914-02-27', '1914-03-28', '1914-04-27', '1914-05-26', '1914-06-25', '1914-07-24', '1914-08-23', '1914-09-21', '1914-10-21', '1914-11-19', '1914-12-19', '1915-01-17', '1915-02-16', '1915-03-17', '1915-04-16', '1915-05-15', '1915-06-14', '1915-07-13', '1915-08-12', '1915-09-10', '1915-10-10', '1915-11-09', '1915-12-09', '1916-01-07', '1916-02-06', '1916-03-06', '1916-04-05', '1916-05-04', '1916-06-03', '1916-07-02', '1916-08-01', '1916-08-30', '1916-09-29', '1916-10-28', '1916-11-27', '1916-12-26', '1917-01-25', '1917-02-23', '1917-03-25', '1917-04-23', '1917-05-23', '1917-06-21', '1917-07-21', '1917-08-19', '1917-09-18', '1917-10-17', '1917-11-16', '1917-12-15', '1918-01-14', '1918-02-12', '1918-03-14', '1918-04-12', '1918-05-12', '1918-06-10', '1918-07-10', '1918-08-08', '1918-09-07', '1918-10-07', '1918-11-06', '1918-12-05', '1919-01-04', '1919-02-02', '1919-03-04', '1919-04-02', '1919-05-02', '1919-05-31', '1919-06-30', '1919-07-29', '1919-08-28', '1919-09-26', '1919-10-26', '1919-11-24', '1919-12-24', '1920-01-22', '1920-02-21', '1920-03-21', '1920-04-20', '1920-05-19', '1920-06-18', '1920-07-17', '1920-08-16', '1920-09-15', '1920-10-15', '1920-11-13', '1920-12-13', '1921-01-11', '1921-02-10', '1921-03-11', '1921-04-10', '1921-05-09', '1921-06-08', '1921-07-07', '1921-08-06', '1921-09-04', '1921-10-04', '1921-11-02', '1921-12-02', '1921-12-31', '1922-01-30', '1922-02-28', '1922-03-30', '1922-04-28', '1922-05-28', '1922-06-26', '1922-07-26', '1922-08-24', '1922-09-23', '1922-10-22', '1922-11-21', '1922-12-20', '1923-01-19', '1923-02-17', '1923-03-19', '1923-04-17', '1923-05-17', '1923-06-15', '1923-07-15', '1923-08-14', '1923-09-13', '1923-10-12', '1923-11-11', '1923-12-10', '1924-01-09', '1924-02-07', '1924-03-08', '1924-04-06', '1924-05-06', '1924-06-04', '1924-07-04', '1924-08-02', '1924-09-01', '1924-09-30', '1924-10-30', '1924-11-28', '1924-12-28', '1925-01-26', '1925-02-25', '1925-03-26', '1925-04-25', '1925-05-24', '1925-06-23', '1925-07-22', '1925-08-21', '1925-09-19', '1925-10-19', '1925-11-17', '1925-12-17', '1926-01-15', '1926-02-14', '1926-03-15', '1926-04-14', '1926-05-13', '1926-06-12', '1926-07-11', '1926-08-10', '1926-09-08', '1926-10-08', '1926-11-06', '1926-12-06', '1927-01-04', '1927-02-03', '1927-03-04', '1927-04-03', '1927-05-02', '1927-06-01', '1927-07-01', '1927-07-31', '1927-08-29', '1927-09-28', '1927-10-27', '1927-11-26', '1927-12-25', '1928-01-24', '1928-02-22', '1928-03-23', '1928-04-21', '1928-05-21', '1928-06-19', '1928-07-19', '1928-08-17', '1928-09-16', '1928-10-15', '1928-11-14', '1928-12-13', '1929-01-12', '1929-02-10', '1929-03-12', '1929-04-10', '1929-05-10', '1929-06-09', '1929-07-09', '1929-08-07', '1929-09-06', '1929-10-05', '1929-11-04', '1929-12-03', '1930-01-02', '1930-01-31', '1930-03-02', '1930-03-31', '1930-04-30', '1930-05-29', '1930-06-28', '1930-07-27', '1930-08-26', '1930-09-24', '1930-10-24', '1930-11-22', '1930-12-22', '1931-01-20', '1931-02-19', '1931-03-20', '1931-04-19', '1931-05-19', '1931-06-18', '1931-07-17', '1931-08-16', '1931-09-14', '1931-10-14', '1931-11-12', '1931-12-12', '1932-01-10', '1932-02-09', '1932-03-09', '1932-04-08', '1932-05-07', '1932-06-06', '1932-07-05', '1932-08-04', '1932-09-02', '1932-10-02', '1932-10-31', '1932-11-30', '1932-12-29', '1933-01-28', '1933-02-26', '1933-03-28', '1933-04-26', '1933-05-26', '1933-06-24', '1933-07-24', '1933-08-22', '1933-09-21', '1933-10-20', '1933-11-19', '1933-12-18', '1934-01-17', '1934-02-15', '1934-03-17', '1934-04-15', '1934-05-15', '1934-06-13', '1934-07-13', '1934-08-11', '1934-09-10', '1934-10-09', '1934-11-08', '1934-12-07', '1935-01-06', '1935-02-04', '1935-03-06', '1935-04-05', '1935-05-05', '1935-06-03', '1935-07-03', '1935-08-01', '1935-08-31', '1935-09-29', '1935-10-29', '1935-11-27', '1935-12-27', '1936-01-25', '1936-02-24', '1936-03-24', '1936-04-23', '1936-05-22', '1936-06-20', '1936-07-20', '1936-08-19', '1936-09-17', '1936-10-17', '1936-11-15', '1936-12-15', '1937-01-13', '1937-02-12', '1937-03-13', '1937-04-12', '1937-05-11', '1937-06-10', '1937-07-09', '1937-08-08', '1937-09-06', '1937-10-06', '1937-11-04', '1937-12-04', '1938-01-03', '1938-02-02', '1938-03-04', '1938-04-02', '1938-05-01', '1938-05-31', '1938-06-29', '1938-07-29', '1938-08-27', '1938-09-26', '1938-10-25', '1938-11-23', '1938-12-23', '1939-01-22', '1939-02-21', '1939-03-23', '1939-04-21', '1939-05-21', '1939-06-19', '1939-07-19', '1939-08-16', '1939-09-15', '1939-10-14', '1939-11-13', '1939-12-12', '1940-01-11', '1940-02-10', '1940-03-11', '1940-04-09', '1940-05-09', '1940-06-07', '1940-07-07', '1940-08-05', '1940-09-04', '1940-10-03', '1940-11-01', '1940-12-01', '1940-12-30', '1941-01-29', '1941-02-28', '1941-03-29', '1941-04-28', '1941-05-27', '1941-06-26', '1941-07-25', '1941-08-24', '1941-09-23', '1941-10-22', '1941-11-20', '1941-12-20', '1942-01-18', '1942-02-17', '1942-03-18', '1942-04-17', '1942-05-16', '1942-06-15', '1942-07-15', '1942-08-14', '1942-09-12', '1942-10-12', '1942-11-10', '1942-12-09', '1943-01-08', '1943-02-07', '1943-03-08', '1943-04-07', '1943-05-06', '1943-06-05', '1943-07-04', '1943-08-03', '1943-09-01', '1943-10-01', '1943-10-30', '1943-11-29', '1943-12-28', '1944-01-27', '1944-02-25', '1944-03-26', '1944-04-24', '1944-05-24', '1944-06-22', '1944-07-22', '1944-08-20', '1944-09-19', '1944-10-18', '1944-11-17', '1944-12-17', '1945-01-16', '1945-02-14', '1945-03-16', '1945-04-14', '1945-05-13', '1945-06-11', '1945-07-11', '1945-08-09', '1945-09-08', '1945-10-07', '1945-11-06', '1945-12-06', '1946-01-05', '1946-02-03', '1946-03-05', '1946-04-03', '1946-05-03', '1946-06-01', '1946-07-01', '1946-07-30', '1946-08-29', '1946-09-27', '1946-10-27', '1946-11-25', '1946-12-25', '1947-01-23', '1947-02-22', '1947-03-23', '1947-04-22', '1947-05-21', '1947-06-20', '1947-07-19', '1947-08-18', '1947-09-16', '1947-10-16', '1947-11-14', '1947-12-14', '1948-01-13', '1948-02-12', '1948-03-12', '1948-04-11', '1948-05-10', '1948-06-09', '1948-07-08', '1948-08-07', '1948-09-05', '1948-10-04', '1948-11-03', '1948-12-02', '1949-01-01', '1949-01-31', '1949-03-01', '1949-03-31', '1949-04-29', '1949-05-29', '1949-06-27', '1949-07-27', '1949-08-25', '1949-09-24', '1949-10-23', '1949-11-22', '1949-12-21', '1950-01-20', '1950-02-18', '1950-03-20', '1950-04-18', '1950-05-18', '1950-06-16', '1950-07-16', '1950-08-14', '1950-09-13', '1950-10-13', '1950-11-12', '1950-12-11', '1951-01-10', '1951-02-08', '1951-03-10', '1951-04-08', '1951-05-08', '1951-06-06', '1951-07-06', '1951-08-04', '1951-09-03', '1951-10-03', '1951-11-02', '1951-12-01', '1951-12-30', '1952-01-28', '1952-02-27', '1952-03-27', '1952-04-26', '1952-05-25', '1952-06-24', '1952-07-23', '1952-08-22', '1952-09-21', '1952-10-21', '1952-11-19', '1952-12-19', '1953-01-17', '1953-02-15', '1953-03-16', '1953-04-15', '1953-05-14', '1953-06-13', '1953-07-12', '1953-08-11', '1953-09-10', '1953-10-10', '1953-11-09', '1953-12-08', '1954-01-06', '1954-02-05', '1954-03-06', '1954-04-05', '1954-05-04', '1954-06-02', '1954-07-01', '1954-07-31', '1954-08-30', '1954-09-29', '1954-10-29', '1954-11-27', '1954-12-26', '1955-01-25', '1955-02-23', '1955-03-25', '1955-04-24', '1955-05-23', '1955-06-21', '1955-07-21', '1955-08-20', '1955-09-18', '1955-10-18', '1955-11-16', '1955-12-15', '1956-01-14', '1956-02-12', '1956-03-13', '1956-04-12', '1956-05-11', '1956-06-09', '1956-07-09', '1956-08-08', '1956-09-07', '1956-10-06', '1956-11-04', '1956-12-03', '1957-01-02', '1957-02-01', '1957-03-03', '1957-04-02', '1957-05-01', '1957-05-31', '1957-06-29', '1957-07-29', '1957-08-27', '1957-09-26', '1957-10-25', '1957-11-23', '1957-12-23', '1958-01-21', '1958-02-20', '1958-03-22', '1958-04-21', '1958-05-20', '1958-06-19', '1958-07-18', '1958-08-17', '1958-09-15', '1958-10-15', '1958-11-13', '1958-12-12', '1959-01-10', '1959-02-09', '1959-03-10', '1959-04-09', '1959-05-09', '1959-06-08', '1959-07-08', '1959-08-06', '1959-09-04', '1959-10-04', '1959-11-02', '1959-12-02', '1959-12-31', '1960-01-29', '1960-02-28', '1960-03-29', '1960-04-27', '1960-05-27', '1960-06-26', '1960-07-25', '1960-08-24', '1960-09-22', '1960-10-22', '1960-11-21', '1960-12-20', '1961-01-19', '1961-02-17', '1961-03-18', '1961-04-16', '1961-05-16', '1961-06-15', '1961-07-14', '1961-08-13', '1961-09-11', '1961-10-11', '1961-11-10', '1961-12-09', '1962-01-08', '1962-02-06', '1962-03-08', '1962-04-06', '1962-05-05', '1962-06-04', '1962-07-03', '1962-08-02', '1962-08-31', '1962-09-30', '1962-10-30', '1962-11-28', '1962-12-28', '1963-01-27', '1963-02-25', '1963-03-27', '1963-04-25', '1963-05-24', '1963-06-23', '1963-07-22', '1963-08-21', '1963-09-19', '1963-10-19', '1963-11-18', '1963-12-17', '1964-01-16', '1964-02-15', '1964-03-15', '1964-04-14', '1964-05-13', '1964-06-11', '1964-07-11', '1964-08-09', '1964-09-07', '1964-10-07', '1964-11-06', '1964-12-05', '1965-01-04', '1965-02-03', '1965-03-05', '1965-04-03', '1965-05-03', '1965-06-01', '1965-06-30', '1965-07-30', '1965-08-28', '1965-09-26', '1965-10-26', '1965-11-25', '1965-12-24', '1966-01-23', '1966-02-22', '1966-03-23', '1966-04-22', '1966-05-22', '1966-06-20', '1966-07-19', '1966-08-18', '1966-09-16', '1966-10-15', '1966-11-14', '1966-12-14', '1967-01-12', '1967-02-11', '1967-03-12', '1967-04-11', '1967-05-11', '1967-06-09', '1967-07-09', '1967-08-07', '1967-09-06', '1967-10-05', '1967-11-04', '1967-12-03', '1968-01-01', '1968-01-31', '1968-03-01', '1968-03-30', '1968-04-29', '1968-05-28', '1968-06-27', '1968-07-27', '1968-08-25', '1968-09-24', '1968-10-23', '1968-11-22', '1968-12-21', '1969-01-19', '1969-02-18', '1969-03-19', '1969-04-18', '1969-05-18', '1969-06-16', '1969-07-16', '1969-08-15', '1969-09-13', '1969-10-13', '1969-11-12', '1969-12-11', '1970-01-09', '1970-02-08', '1970-03-09', '1970-04-07', '1970-05-07', '1970-06-05', '1970-07-05', '1970-08-04', '1970-09-03', '1970-10-03', '1970-11-01', '1970-12-01', '1970-12-30', '1971-01-28', '1971-02-27', '1971-03-28', '1971-04-26', '1971-05-26', '1971-06-24', '1971-07-24', '1971-08-23', '1971-09-22', '1971-10-21', '1971-11-20', '1971-12-19', '1972-01-18', '1972-02-16', '1972-03-17', '1972-04-15', '1972-05-14', '1972-06-13', '1972-07-12', '1972-08-11', '1972-09-10', '1972-10-09', '1972-11-08', '1972-12-08', '1973-01-06', '1973-02-05', '1973-03-06', '1973-04-05', '1973-05-04', '1973-06-02', '1973-07-02', '1973-07-31', '1973-08-30', '1973-09-28', '1973-10-28', '1973-11-26', '1973-12-26', '1974-01-25', '1974-02-24', '1974-03-25', '1974-04-24', '1974-05-23', '1974-06-21', '1974-07-21', '1974-08-20', '1974-09-18', '1974-10-17', '1974-11-15', '1974-12-15', '1975-01-14', '1975-02-13', '1975-03-14', '1975-04-13', '1975-05-13', '1975-06-11', '1975-07-10', '1975-08-09', '1975-09-07', '1975-10-06', '1975-11-05', '1975-12-04', '1976-01-03', '1976-02-01', '1976-03-02', '1976-04-01', '1976-05-01', '1976-05-30', '1976-06-29', '1976-07-29', '1976-08-27', '1976-09-25', '1976-10-24', '1976-11-23', '1976-12-22', '1977-01-21', '1977-02-19', '1977-03-21', '1977-04-20', '1977-05-19', '1977-06-18', '1977-07-18', '1977-08-16', '1977-09-15', '1977-10-14', '1977-11-13', '1977-12-12', '1978-01-10', '1978-02-09', '1978-03-10', '1978-04-09', '1978-05-09', '1978-06-07', '1978-07-07', '1978-08-06', '1978-09-04', '1978-10-04', '1978-11-02', '1978-12-01', '1978-12-31', '1979-01-29', '1979-02-28', '1979-03-29', '1979-04-28', '1979-05-27', '1979-06-26', '1979-07-26', '1979-08-24', '1979-09-23', '1979-10-22', '1979-11-21', '1979-12-20', '1980-01-19', '1980-02-17', '1980-03-18', '1980-04-16', '1980-05-15', '1980-06-14', '1980-07-13', '1980-08-12', '1980-09-11', '1980-10-10', '1980-11-09', '1980-12-09', '1981-01-07', '1981-02-06', '1981-03-07', '1981-04-06', '1981-05-05', '1981-06-04', '1981-07-03', '1981-08-01', '1981-08-31', '1981-09-29', '1981-10-29', '1981-11-28', '1981-12-28', '1982-01-26', '1982-02-25', '1982-03-26', '1982-04-25', '1982-05-24', '1982-06-23', '1982-07-22', '1982-08-20', '1982-09-18', '1982-10-18', '1982-11-17', '1982-12-17', '1983-01-15', '1983-02-14', '1983-03-16', '1983-04-14', '1983-05-14', '1983-06-12', '1983-07-12', '1983-08-10', '1983-09-08', '1983-10-07', '1983-11-06', '1983-12-06', '1984-01-04', '1984-02-03', '1984-03-04', '1984-04-03', '1984-05-02', '1984-06-01', '1984-06-30', '1984-07-29', '1984-08-28', '1984-09-26', '1984-10-26', '1984-11-24', '1984-12-24', '1985-01-22', '1985-02-21', '1985-03-23', '1985-04-21', '1985-05-21', '1985-06-20', '1985-07-19', '1985-08-17', '1985-09-16', '1985-10-15', '1985-11-14', '1985-12-13', '1986-01-11', '1986-02-10', '1986-03-12', '1986-04-10', '1986-05-10', '1986-06-09', '1986-07-08', '1986-08-07', '1986-09-05', '1986-10-05', '1986-11-03', '1986-12-03', '1987-01-01', '1987-01-31', '1987-03-01', '1987-03-31', '1987-04-29', '1987-05-29', '1987-06-27', '1987-07-27', '1987-08-26', '1987-09-24', '1987-10-24', '1987-11-22', '1987-12-22', '1988-01-20', '1988-02-19', '1988-03-19', '1988-04-18', '1988-05-17', '1988-06-15', '1988-07-15', '1988-08-14', '1988-09-12', '1988-10-12', '1988-11-11', '1988-12-10', '1989-01-09', '1989-02-07', '1989-03-09', '1989-04-07', '1989-05-06', '1989-06-05', '1989-07-05', '1989-08-03', '1989-09-01', '1989-10-01', '1989-10-31', '1989-11-29', '1989-12-29', '1990-01-28', '1990-02-26', '1990-03-28', '1990-04-26', '1990-05-25', '1990-06-24', '1990-07-23', '1990-08-22', '1990-09-20', '1990-10-20', '1990-11-19', '1990-12-18', '1991-01-17', '1991-02-16', '1991-03-17', '1991-04-16', '1991-05-15', '1991-06-14', '1991-07-13', '1991-08-11', '1991-09-09', '1991-10-09', '1991-11-08', '1991-12-07', '1992-01-06', '1992-02-05', '1992-03-06', '1992-04-04', '1992-05-04', '1992-06-02', '1992-07-02', '1992-07-31', '1992-08-29', '1992-09-27', '1992-10-27', '1992-11-26', '1992-12-25', '1993-01-24', '1993-02-23', '1993-03-24', '1993-04-23', '1993-05-23', '1993-06-21', '1993-07-20', '1993-08-19', '1993-09-17', '1993-10-17', '1993-11-15', '1993-12-14', '1994-01-13', '1994-02-12', '1994-03-13', '1994-04-12', '1994-05-12', '1994-06-11', '1994-07-10', '1994-08-08', '1994-09-07', '1994-10-06', '1994-11-05', '1994-12-04', '1995-01-02', '1995-02-01', '1995-03-03', '1995-04-01', '1995-05-01', '1995-05-31', '1995-06-29', '1995-07-29', '1995-08-27', '1995-09-26', '1995-10-25', '1995-11-24', '1995-12-23', '1996-01-21', '1996-02-20', '1996-03-21', '1996-04-19', '1996-05-19', '1996-06-17', '1996-07-17', '1996-08-15', '1996-09-14', '1996-10-14', '1996-11-12', '1996-12-12', '1997-01-10', '1997-02-09', '1997-03-10', '1997-04-09', '1997-05-08', '1997-06-06', '1997-07-06', '1997-08-05', '1997-09-03', '1997-10-03', '1997-11-02', '1997-12-01', '1997-12-31', '1998-01-29', '1998-02-28', '1998-03-29', '1998-04-27', '1998-05-27', '1998-06-25', '1998-07-25', '1998-08-23', '1998-09-22', '1998-10-22', '1998-11-22', '1998-12-20', '1999-01-19', '1999-02-17', '1999-03-19', '1999-04-17', '1999-05-16', '1999-06-15', '1999-07-14', '1999-08-13', '1999-09-11', '1999-10-11', '1999-11-09', '1999-12-09', '2000-01-08', '2000-02-07', '2000-03-07', '2000-04-06', '2000-05-05', '2000-06-04', '2000-07-03', '2000-08-01', '2000-08-30', '2000-09-29', '2000-10-28', '2000-11-27', '2000-12-27', '2001-01-26', '2001-02-24', '2001-03-26', '2001-04-25', '2001-05-24', '2001-06-23', '2001-07-22', '2001-08-20', '2001-09-18', '2001-10-18', '2001-11-16', '2001-12-16', '2002-01-15', '2002-02-13', '2002-03-15', '2002-04-14', '2002-05-14', '2002-06-12', '2002-07-12', '2002-08-10', '2002-09-08', '2002-10-07', '2002-11-06', '2002-12-05', '2003-01-04', '2003-02-02', '2003-03-04', '2003-04-03', '2003-05-03', '2003-06-01', '2003-07-01', '2003-07-30', '2003-08-29', '2003-09-27', '2003-10-27', '2003-11-25', '2003-12-24', '2004-01-23', '2004-02-21', '2004-03-22', '2004-04-21', '2004-05-20', '2004-06-19', '2004-07-19', '2004-08-17', '2004-09-16', '2004-10-15', '2004-11-14', '2004-12-13', '2005-01-11', '2005-02-10', '2005-03-11', '2005-04-10', '2005-05-09', '2005-06-08', '2005-07-08', '2005-08-06', '2005-09-05', '2005-10-05', '2005-11-03', '2005-12-03', '2006-01-01', '2006-01-31', '2006-03-01', '2006-03-30', '2006-04-29', '2006-05-28', '2006-06-27', '2006-07-26', '2006-08-25', '2006-09-24', '2006-10-23', '2006-11-22', '2006-12-22', '2007-01-20', '2007-02-19', '2007-03-20', '2007-04-18', '2007-05-18', '2007-06-16', '2007-07-16', '2007-08-14', '2007-09-13', '2007-10-12', '2007-11-11', '2007-12-11', '2008-01-10', '2008-02-08', '2008-03-09', '2008-04-07', '2008-05-06', '2008-06-05', '2008-07-04', '2008-08-03', '2008-09-01', '2008-09-30', '2008-10-30', '2008-11-29', '2008-12-29', '2009-01-27', '2009-02-26', '2009-03-27', '2009-04-26', '2009-05-25', '2009-06-24', '2009-07-23', '2009-08-21', '2009-09-20', '2009-10-19', '2009-11-18', '2009-12-17', '2010-01-16', '2010-02-15', '2010-03-17', '2010-04-15', '2010-05-15', '2010-06-13', '2010-07-13', '2010-08-11', '2010-09-09', '2010-10-09', '2010-11-07', '2010-12-07', '2011-01-05', '2011-02-04', '2011-03-06', '2011-04-04', '2011-05-04', '2011-06-03', '2011-07-02', '2011-08-01', '2011-08-30', '2011-09-28', '2011-10-28', '2011-11-26', '2011-12-26', '2012-01-24', '2012-02-23', '2012-03-23', '2012-04-22', '2012-05-22', '2012-06-21', '2012-07-20', '2012-08-19', '2012-09-17', '2012-10-16', '2012-11-15', '2012-12-14', '2013-01-13', '2013-02-11', '2013-03-13', '2013-04-11', '2013-05-11', '2013-06-10', '2013-07-09', '2013-08-08', '2013-09-07', '2013-10-06', '2013-11-04', '2013-12-04', '2014-01-02', '2014-02-01', '2014-03-02', '2014-04-01', '2014-04-30', '2014-05-30', '2014-06-28', '2014-07-28', '2014-08-27', '2014-09-25', '2014-10-25', '2014-11-24', '2014-12-23', '2015-01-22', '2015-02-20', '2015-03-21', '2015-04-20', '2015-05-19', '2015-06-18', '2015-07-18', '2015-08-16', '2015-09-15', '2015-10-14', '2015-11-13', '2015-12-12', '2016-01-11', '2016-02-10', '2016-03-10', '2016-04-08', '2016-05-08', '2016-06-06', '2016-07-05', '2016-08-04', '2016-09-03', '2016-10-02', '2016-11-01', '2016-12-01', '2016-12-30', '2017-01-29', '2017-02-28', '2017-03-29', '2017-04-27', '2017-05-27', '2017-06-25', '2017-07-24', '2017-08-23', '2017-09-21', '2017-10-21', '2017-11-20', '2017-12-19', '2018-01-18', '2018-02-17', '2018-03-19', '2018-04-17', '2018-05-16', '2018-06-15', '2018-07-14', '2018-08-12', '2018-09-11', '2018-10-10', '2018-11-09', '2018-12-08', '2019-01-07', '2019-02-06', '2019-03-08', '2019-04-06', '2019-05-06', '2019-06-05', '2019-07-04', '2019-08-02', '2019-08-31', '2019-09-30', '2019-10-29', '2019-11-28', '2019-12-27', '2020-01-26', '2020-02-25', '2020-03-25', '2020-04-24', '2020-05-24', '2020-06-22', '2020-07-22', '2020-08-20', '2020-09-18', '2020-10-18', '2020-11-16', '2020-12-16', '2021-01-14', '2021-02-13', '2021-03-14', '2021-04-13', '2021-05-13', '2021-06-12', '2021-07-11', '2021-08-10', '2021-09-08', '2021-10-07', '2021-11-06', '2021-12-05', '2022-01-04', '2022-02-02', '2022-03-04', '2022-04-02', '2022-05-02', '2022-06-01', '2022-06-30', '2022-07-30', '2022-08-28', '2022-09-27', '2022-10-27', '2022-11-25', '2022-12-24'];

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