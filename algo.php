<?php 

function callAlgo() {
    foreach ($GLOBALS["queries"] as $query) {
        $ret = algo($query);
        if ($ret == 'T') {
            echo $query . " is true.\n";
        } else if ($ret == 'F') {
            echo $query . " is false.\n";
        } else if ($ret == "C") {
            echo "There is a contradiction with query " . $query . "\n";
        } else if (strpos($ret, 'U') !== false) {
            echo $query . " is not determined.\n";
        }
    }
}


function algo($char) {
    $arrKey = [];
    if (in_array($char, $GLOBALS["facts"]) && !(array_key_exists("!" . $char, $GLOBALS["graph"]))) {
        return "T";
    } else {
        foreach ($GLOBALS["graph"] as $key => $value) {
            if (strpos($key, $char) !== false && strlen($key) > 2) {
              $arrKey[] = $key;
            }
        }
        if (array_key_exists($char, $GLOBALS["graph"]) || array_key_exists("!" . $char, $GLOBALS["graph"]) || count($arrKey) != 0) {
            $arrDepRes = array("not!" => array(), "!" => array(), '0' => array());
            if (in_array($char, $GLOBALS["facts"])) {
                $arrDepRes["not!"][] = "T";
            }
            if (array_key_exists($char, $GLOBALS["graph"])) {
                foreach ($GLOBALS["graph"][$char] as $depends) {
                    $length = strlen($depends);
                    $tmpStr = "";
                    for ($i = 0; $i < $length; $i++) {
                        if (preg_match('#[A-Z]#', $depends[$i])) {
                            $tmpStr .= algo($depends[$i]);
                        } else {
                            $tmpStr .= $depends[$i];
                        }
                    }
                    $arrDepRes["not!"][] = bracket($tmpStr);
                }
            }
            if (array_key_exists("!" . $char, $GLOBALS["graph"])) {
                foreach ($GLOBALS["graph"]["!" . $char] as $depends) {
                    $length = strlen($depends);
                    $tmpStr = "";
                    for ($i = 0; $i < $length; $i++) {
                        if (preg_match('#[A-Z]#', $depends[$i])) {
                            $tmpStr .= algo($depends[$i]);
                        } else {
                            $tmpStr .= $depends[$i];
                        }
                    }
                    $arrDepRes["!"][] = bracket($tmpStr);
                }
            }
            foreach($arrKey as $key) {
                foreach ($GLOBALS["graph"][$key] as $depends) {
                    $length = strlen($depends);
                    $tmpStr = "";
                    for ($i = 0; $i < $length; $i++) {
                        if (preg_match('#[A-Z]#', $depends[$i])) {
                            $tmpStr .= algo($depends[$i]);
                        } else {
                            $tmpStr .= $depends[$i];
                        }
                    }
                    $arrDepRes["0"][$key] = bracket($tmpStr);
                }
            }
            return checkContrad($arrDepRes, $char);
        } else {
            return "F";
        }
    }
}


function checkContrad($arr, $char) {
    $ret = "";
    if (in_array("T", $arr["not!"]) || in_array("T", $arr["not!"]) && in_array("U", $arr["not!"]) || in_array("T", $arr["not!"]) && in_array("F", $arr["not!"])) {
        $ret = "T";;
    } else if (in_array("U", $arr["not!"]) || in_array("U", $arr["not!"]) && in_array("F", $arr["not!"])) {
        $ret = "U";
    } else if (in_array("F", $arr["not!"])) {
        $ret = 'F';
    }
    if (in_array("T", $arr["!"]) || in_array("T", $arr["!"]) && in_array("U", $arr["!"]) || in_array("T", $arr["!"]) && in_array("F", $arr["!"])) {
        if ($ret == "T") {
            $ret = "C";
        } else {
            $ret = "F";
        }
    } else if (in_array("U", $arr["!"]) || in_array("U", $arr["!"]) && in_array("F", $arr["!"])) {
        if ($ret != "T") {
            $ret = "U";
        }
    } else if (in_array("F", $arr["!"])) {
        if ($ret != "T") {
            $ret = "F";
        }
    }
    if (in_array("T", $arr['0']) && ($ret == 'U' || $ret == 'F' || $ret == '')) {
        $ret .= 'U[';
        foreach($arr['0'] as $key => $value) {
            if ($value == "T") {
                $ret .= $key;
                $ret .= ';' . $char;
            }
        }
        $ret .= ']';
    } else if (in_array("F", $arr["0"])) {
        if ($ret == "") {
            $ret = "F";
        }
    }
    return $ret;
}



function bracket($str) {
    $i = 0;
    $length = strlen($str);
    $openB = [];
    $closeB = [];
    while ($i < $length) {
        if ($str[$i] == '(') {
            $openB[] = $i;
        } else if ($str[$i] == ')') {
            $closeB[] = $i;
        }
        $c = count($openB);
        $i++;
        if ($i == ($length) && $c != 0) {
            $tmp = substr($str, $openB[$c - 1], $closeB[0] - $openB[$c -1] + 1);
            $res = resolveStr(substr($tmp, 1, strlen($tmp) - 2));
            $str = str_replace($tmp, $res, $str);
            $openB = [];
            $closeB = [];
            $i = 0;
        }
    }
    
    return resolveStr($str);
}


function resolveStr($str) {
    $undeter = checkU($str);
    $countU = 0;
    $str = str_replace("!F", "T", $str);
    $str = str_replace("!T", "F", $str);
    $tmpStr2 = "";
    while(strlen($str) > 1 && $tmpStr != $str) {
        while (($p = strpos($str, '+')) !== false) {
            $tmpStr = substr($str, $p - 1, 3);
            if ((strpos($tmpStr, 'T') !== false) && (strpos($tmpStr, 'F') === false) && (strpos($tmpStr, 'U') === false)) {
                $str = str_replace($tmpStr, 'T', $str);
            } else if ((strpos($tmpStr, 'T') !== false) && (strpos($tmpStr, 'U') !== false)) {
                $str = str_replace($tmpStr, 'U', $str);
                $tmpStr2 = '[' . $undeter[$countU][0] . ';' . $undeter[$countU][1] . ']' ;
                if ($undeter[$countU + 1] != NULL) {
                    $countU++;
                }
            } else {
                $str = str_replace($tmpStr, 'F', $str);
            }
        }
        while (($p = strpos($str, '|')) !== false) {
            $tmpStr = substr($str, $p - 1, 3);
            if (strpos($tmpStr, 'T') !== false && strpos($tmpStr, 'F') !== false || strpos($tmpStr, 'T') !== false && strpos($tmpStr, 'F') === false || strpos($tmpStr, 'T') !== false && strpos($tmpStr, 'U') !== false) {
                $str = str_replace($tmpStr, 'T', $str);
            } else if (strpos($tmpStr, 'F') !== false && strpos($tmpStr, 'U') !== false) {
                $str = str_replace($tmpStr, 'U', $str);
                $countU++;
            } else if (strpos($tmpStr, 'U') !== false) {
                if ($undeter[$countU][0] == $undeter[$countU + 1][0]) {
                    if ($undeter[$countU][1] != $undeter[$countU + 1][1]) {
                        $str = str_replace($tmpStr, 'T', $str);
                    } else {
                        $str = str_replace($tmpStr, 'U', $str);
                    }
                } else {
                    $str = str_replace($tmpStr, 'U', $str);
                }
                $countU += 2;
            } else {
                $str = str_replace($tmpStr, 'F', $str);
            }
        } 
        while (($p = strpos($str, '^')) !== false) {
            $tmpStr = substr($str, $p - 1, 3);
            if (strpos($tmpStr, 'T') !== false && strpos($tmpStr, 'F') !== false) {
                $str = str_replace($tmpStr, 'T', $str);
            } else if (strpos($tmpStr, 'F') !== false && strpos($tmpStr, 'U') !== false || strpos($tmpStr, 'T') !== false && strpos($tmpStr, 'U') !== false) {
                $str = str_replace($tmpStr, 'U', $str);
                $countU++;
            } else if (strpos($tmpStr, 'U') !== false) {
                if ($undeter[$countU][0] == $undeter[$countU + 1][0]) {
                    if ($undeter[$countU][1] != $undeter[$countU + 1][1]) {
                        $str = str_replace($tmpStr, 'T', $str);
                    } else {
                        $str = str_replace($tmpStr, 'F', $str);
                    }
                } else {
                    $str = str_replace($tmpStr, 'U', $str);
                }
                $countU += 2;
            } else {
                $str = str_replace($tmpStr, 'F', $str);
            }
        } 
    }
    if ($tmpStr2 != "") {
        $str .= $tmpStr2;
    }
    return $str;
}


function checkU(& $str) {
    $arr = [];
    $i = 0;
    $length = strlen($str);
    $first = 0;
    $next = 0;
    while ($i < $length) {
        if ($str[$i] == '[') {
            $first = $i;
        } else if ($str[$i] == ']') {
            $next = $i;
        }
        if ($first != 0 && $next != 0) {
            $tmp = substr($str, $first, $next - $first + 1);
            $pos = strpos($str, $tmp);
            if ($pos !== false) {
                $str = substr_replace($str, '', $pos, strlen($tmp));
            }
            $tmp2 = substr($tmp, 1, strlen($tmp) - 2);
            $arr[] = explode(';', $tmp2);
            $i = 0;
            $first = 0;
            $next = 0;
        }
        $i++;
    }
    return $arr;
}

?>