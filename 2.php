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

function getSqlString($inputArray, $fields)
{
    $str = "";
    foreach ($inputArray as $value) {
        $str .= "('";
        foreach ($fields as $field) {
            $str .= $value[$field]."', '";
        }
        $str = substr($str, 0, -3) . "), ";
    }
    $str = substr($str, 0, -2);
    return $str;
}

function checkProductExist($xml, $code)
{
    $res = false;
    foreach ($xml->children() as $value) {
        $res = $value->attributes()['Код'] == $code ? $value : $res;
    }
    return $res;
}
function getElemOrNull($array, $keyCondition, $valueCondition)
{
    $res = null;
    foreach ($array as $key => $value) {
        if ((($keyCondition === true) || ((string)$keyCondition === (string)$key)) &&
            (($valueCondition === true) || ((string)$valueCondition === (string)$value))) {
            $res = $value;
        }
    }
    return $res;
}

function importXml($a)
{
    $xml = simplexml_load_file($a);
    $mysql = new mysqli("127.0.0.1", "root", "root", "test_samson");
    $arrayProducts = [];
    $arrayPrices = [];
    $arrayProperties = [];
    $arrayCategories = [];
    $counter = 0; $counter2 = 0;
    foreach ($xml as $value)
    {
        $productID = time() + $counter;
        array_push($arrayProducts, [
            'id'=>$productID,
            'code'=>strval($value->attributes()->{'Код'}),
            'name'=>strval($value->attributes()->{'Название'})
        ]);
        foreach($value->children()->{'Цена'} as $v) {
            array_push($arrayPrices, ['product_id'=>$productID, 'price_type'=>strval($v->attributes()->{'Тип'}), 'price'=>strval($v)]);
        }
        foreach ($value->{'Свойства'}->children() as $k => $v) {
            $attr = $v->attributes();
            array_push($arrayProperties, ['product_id'=>$productID, 'name'=>strval($k), 'value'=>strval($v), 'unit'=>empty($attr)? 'null':$attr]);
        }
        $time = time();
        foreach ($value->{'Разделы'}->children() as $v) {
            $id = $time + 1001 + $counter2;
            $key = array_search(strval($v), array_column($arrayCategories, 'name'));
            $code = $key!==false ? $arrayCategories[$key]['code'] : ($productID + $counter2) % 10000;
            array_push($arrayCategories, ['id'=>$id, 'code'=>$code, 'product_id'=>$productID, 'name'=>strval($v)]);
            $counter2++;
        }
        $counter++;
    }

    $str1 = getSqlString($arrayProducts, ['id', 'code', 'name']);
    $sql1 = "INSERT INTO `a_product`(id, code, name) VALUES $str1";
    $mysql->query($sql1);
    $str2 = getSqlString($arrayPrices, ['product_id', 'price_type', 'price']);
    $sql2 = "INSERT INTO `a_price`(product_id, price_type, price) VALUES $str2";
    $mysql->query($sql2);
    $str3 = getSqlString($arrayProperties, ['product_id', 'name', 'value', 'unit']);
    $sql3 = "INSERT INTO `a_property`(product_id, name, value, unit) VALUES $str3";
    $mysql->query($sql3);
    $str4 = getSqlString($arrayCategories, ['id', 'code', 'product_id', 'name']);
    $sql4 = "INSERT INTO `a_category`(id, code, product_id, name) VALUES $str4";
    $mysql->query($sql4);
}


echo '<pre>';

echo '</pre>';