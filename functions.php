<?php

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

function array2option($array, $selected = false)
{
    $result = '';
    foreach ($array as $key => $val) {
        $sel = $key == $selected ? ' selected="selected"' : '';
        $result .= '<option value="' . $key . '" ' . $sel . '>' . $val . '</option>';
    }
    return $result;
}

function range2option($start, $end, $selected = false)
{
    $result = '';
    for ($i = $start; $i <= $end; $i++) {
        $sel = $i == $selected ? ' selected="selected"' : '';
        $result .= '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
    }
    return $result;
}
function range2optionDesc($start, $end, $selected = false)
{
    $result = '';
    for ($i = $start; $i >= $end; $i--) {
        $sel = $i == $selected ? ' selected="selected"' : '';
        $result .= '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
    }
    return $result;
}

function showError($error){
    echo '<div class="alert alert-danger" role="alert">
  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  <span class="sr-only">Error:</span>
  '.$error.'
</div>';
}