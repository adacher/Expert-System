<?php 

function isFileEmpty($arr) {
    clearstatcache();
    if(filesize($arr)) {
        return 1;
    }
    return 0;
}

function checkRetValue_one($val) {
    if ($val == 2){
        echo "Fact error" . "\n";
    }
    else if ($val == 3){
        echo "Query error" . "\n";
    }
    else {
        echo "Rule error" . "\n";                    
    }
    return $val;
}

function checkRetValue_two($val){
    if ($val == 1){
        echo "Syntax error" . "\n";
        return 1;
    }
    else if ($val == 2){
        echo "Missing query" . "\n";
        return 1;
    }
    else if ($val == 3){
        echo "Missing fact" . "\n";
        return 1;
    }
    else if ($val == 5){
        echo "Too many facts" . "\n";
        return 1;
    }
    else if ($val == 6){
        echo "Too many queries" . "\n";
        return 1;
    }
    else {
        echo "Missing rule" . "\n";
        return 1;
    }
}

function inputFact($argv)
{
    if ($argv == "Fact"){
        echo "Enter new fact: ";
        $input = rtrim(fgets(STDIN));            
        if (preg_match("/^\=[A-Z]+$/", $input) || ($input[0] == '=' && $input[1] == NULL)) {
            return $input;                
        }
        else {
            echo "New facts are not valid." . "\n";
            return 1;
        }
    }
    return 0;
}

function checkFile($arr) {
    $i = 0;
    $checkQ = 0;
    $checkF = 0;
    $checkR = 0;
    while ($i < count($arr)) {
        if (preg_match("/^[A-Z\+\=\<\>\|\^\!\?\(\)]+$/", $arr[$i]) == 0) {
            return 1;
        }       
        if (preg_match("/\=\>/", $arr[$i]) || preg_match("/\<\=\>/", $arr[$i])){
            $checkR++;
        }        
        if ($arr[$i][0] == '=' && $i != 0){
            $checkF++;
        }       
        if ($arr[$i][0] == '?'){
            $checkQ++;
        }
        $i++;
    }
    if ($checkR < 1){
        return 4;
    }
    if ($checkF < 1){
        return 3;
    }
    if ($checkF > 1){
        return 5;
    }
    if ($checkQ < 1) {
        return 2;
    }
    if ($checkQ > 1) {
        return 6;
    }
    return 0;
}


function delComms(& $arr) {
    $i = 0;
    $nbElem = count($arr);
    while ($i < $nbElem) {
        $arr[$i] = preg_replace('/\s+/', '', $arr[$i]);
        if ($arr[$i][0] == '#' || $arr[$i] == NULL) {
            unset($arr[$i]);            
        }
        else if (($pos = strpos($arr[$i], '#')) !== FALSE) {
            $arr[$i] = substr($arr[$i], 0 , $pos);
        }
        $i++;
    }
    $arr = array_values($arr);
}

function checkFacts($line) {
    $tmpStr = substr($line, 1, strlen($line));
    if (!preg_match("#^[A-Z]+$#", $tmpStr) && $tmpStr != '') {
        return 2;
    } else {
        storeFacts($line);
        return 0;
    }
}

function checkQueries($line) {
    $tmpStr = substr($line, 1, strlen($line));
    if (!preg_match("#^[A-Z]+$#", $tmpStr)) {
        return 3;
    } else {
        storeQueries($line);
        return 0;
    }
}


function is_Sign($char){
    if ($char == '+' || $char == '=' || $char == '<' || $char == '>' || $char == '|' || $char == '!' || $char == '^') {
        return 1;
    }
    else {
        return 0;
    }
}

function checkRules($arr) {
    $i = 0;
    $n = 0;
    $countO = 0;
    $countC = 0;
    $countEq = 0;
    $nbElem = count($arr);
    if ($nbElem == 0){
        return 4;
    }
    while ($i < $nbElem){        
        $nbCharacter = strlen($arr[$i]);
        if (preg_match("/\?/", $arr[$i])){            
            return 4;
        }        
        $firstCharacter = $arr[$i][0];
        $lastCharacter = substr($arr[$i], -1);
        if (!preg_match("#^[\!\(A-Z]$#", $firstCharacter) || (!preg_match("#^[A-Z\)]$#", $lastCharacter))) {
            return 4;
        }
        else if (preg_match("/(\|{2}|\+{2}|\!{2}|\^{2}|\={2}|\>{2}|\<{2})/", $arr[$i])){
            return 4;
        }       
        else if (preg_match("/\w*[A-Z]\w*[A-Z]\w*/", $arr[$i])){
            return 4;
        }
        else if (preg_match("/[A-Z]\!/", $arr[$i])){
            return 4;
        }
        for ($n = 0; $n < $nbCharacter; $n++){
            $next = 0;                
            $currentChar = substr($arr[$i], $n, 1);
            if ($countC > $countO) {
                return 4;
            }
            if (is_Sign($currentChar) == 1){
                $next = $n + 1;
                $nextChar = substr($arr[$i], $next, 1);  
                if ($currentChar == '<') {
                   if ($nextChar == '='){
                       $x = $next + 1;
                       $xChar = substr($arr[$i], $x, 1);
                        if ($xChar != '>') {
                            return 4;
                        }
                   }
                   if ($nextChar != '='){ 
                       return 4;
                   }
                }
                if ($currentChar == '=') {						
                    if ($nextChar != '>') {
                        return 4;
                    }
                    $countEq++;
                }
                if ($currentChar == '>') {
                    $prev = $n - 1;
                    $prevChar = substr($arr[$i], $prev, 1);
                    if ($prevChar != '=') {
                        return 4;
                    }						
                }
                if ($currentChar != '=' && $currentChar != '<') {
                    if (is_Sign($nextChar) == 1 && $nextChar != '!') {
                        return 4;
                    }
                }
            }
            if ($currentChar == '(') {
                $next = $n + 1;
                $nextChar = substr($arr[$i], $next, 1);
                if ($n != 0) {
                    $prev = $n - 1;
                    $prevChar = substr($arr[$i], $prev, 1);				
                    if (is_Sign($prevChar) == 0 && $prevChar != '(') {
                        return 4;
                    }
                }					
                if (!preg_match("/[A-Z]/", $arr[$i][$next]) && $nextChar != '!' && $nextChar != '('){
                    return 4;
                }
                $countO++;
            }
            if ($currentChar == ')') {
                $prev = $n - 1;
                $prevChar = substr($arr[$i], $prev, 1);
                if (!preg_match("/[A-Z]/", $arr[$i][$prev]) && $prevChar != ')') {
                    return 4;
                }
                $countC++;
            }
        }
        $i++;
        if ($countEq != 1) {
            return 4;
        }
        $countEq = 0;
    }
    if ($countC != $countO) {
        return 4;
    }
    storeRules($arr);
    return 0;
}

?>