<?php
function convertString($a, $b)
{
    $result = $a;
    $pos = strpos($a, $b);
    if ($pos) {
        $tempString = substr($a, $pos + 1);
        $pos2 = strpos($tempString, $b);
        if ($pos2) {
            $part1 = substr($a, 0, $pos + 1);
            $replacement = strrev(substr($tempString, $pos2, strlen($b)));
            $part2 = substr_replace($tempString, $replacement, $pos2, strlen($b));
            $result = $part1 . $part2;
        }
    }
    return $result;
}

function mySortForKey($a, $b)
{
    function build_sorter($key, $inpArray) {
        return function ($a, $b) use ($key, $inpArray) {
            try {
                $existInA = array_key_exists($key, $a);
                $existInB = array_key_exists($key, $b);
                if ($existInA && $existInB) {
                    return strnatcmp($a[$key], $b[$key]);
                } else {
                    $wrongArr = $existInA ? $b : $a;
                    $i = array_search($wrongArr, $inpArray);
                    throw new Exception('<br/>Неверный вложенный массив с индексом '.$i);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                return 0;
            }
        };
    }
    usort($a, build_sorter($b, $a));
    return $a;
}
