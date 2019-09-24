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

function exportXml($a, $b)
{
    $mysqli = new mysqli("127.0.0.1", "root", "root", "test_samson");
    $query = "SELECT product.code as 'Код', product.name as 'Название',
                    price.price as 'Цена', price.price_type,
                    prop.name, prop.value, prop.unit,
                    category.name as 'Раздел'
            FROM a_product as product
            JOIN a_price as price on product.id = price.product_id
            JOIN a_property as prop on product.id = prop.product_id
            JOIN a_category as category on product.id = category.product_id
                WHERE category.code in (
                SELECT head_category_code as code
                FROM dependence_categories
                WHERE head_category_code = 6883
                union
                SELECT adjective_category_code as code
                FROM dependence_categories
                WHERE head_category_code = 6883
                )";
    $dom = new DOMDocument( '1.0', 'utf-8' );
    $dom->formatOutput = True;
    $root = $dom->createElement('Товары');
    $dom->appendChild($root);
    $result = $mysqli->query($query);
    echo "<br/>";
    $root_element = "Товары";
    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><{$root_element}></{$root_element}>");
    while($result_array = $result->fetch_assoc()){
        $productMB = checkProductExist($xml, $result_array['Код']);
        $product = $productMB;
        if (!$productMB) {
            $product = $xml->addChild("Товар");
            $product->addChild("Цена");
            $product->addChild("Цена");
        }
        foreach($result_array as $key => $value)
        {
            var_dump("result_array", $result_array, 'key', $key, 'value', $value);
            switch ($key) {
                case 'Код':
                case 'Название':
                    if (!$product->attributes()[$key]) {
                        $product->addAttribute($key, $value);
                    }
                    break;
                case 'price_type':
                    $elem = getElemOrNull($product->{'Цена'}, true, $result_array['Цена']);
                    if (!is_null($elem) && !$elem->attributes()["Тип"]){
                        $elem->addAttribute("Тип", $value);
                    }
                    break;

                case 'name':
                    $elem = getElemOrNull($product->children(), 'Свойства', true);
                    if (is_null($elem)){
                        $product->addChild("Свойства");
                    }
                    $elem = getElemOrNull($product->{'Свойства'}->children(), $value, true);
                    if (is_null($elem)){
                        $product->{'Свойства'}->addChild($value);
                    }
                    break;
                case 'value':
                    $isset = false;
                    foreach ($product->{'Свойства'}->children() as $k => $v) {
                        $isset = (((string)$result_array['name'] === $k) && ($value !== $v))? true : $isset;
                    }
                    if ($isset) {
                        $product->{'Свойства'}->{$result_array['name']} = $value;
                    }
                    break;
                case 'unit':
                    if ($value !== 'null') {
                        try {
                            $elem = getElemOrNull($product->{'Свойства'}->children(), $result_array['name'], true);
                            if (!is_null($elem)) {
                                $elem->addAttribute("ЕдИзм", $value);
                            } else {
                                throw new Exception('Неверный атрибут "ЕдИзм"');
                            }
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                    break;
                case 'Раздел':
                    $elem = getElemOrNull($product->children(), 'Разделы', true);
                    if (is_null($elem)){
                        $product->addChild("Разделы");
                    }
                    $elem = getElemOrNull($product->{'Разделы'}->children(), true, $value);
                    if (is_null($elem)){
                        $product->{'Разделы'}->addChild('Раздел', $value);
                    }
                    break;
                case 'Цена':
                    $arr = $product->children()->{'Цена'};
                    $filledArray = [];
                    $count = count($arr);
                    for ($i = 0; $i < $count; $i++) {
                        if (!empty($arr[$i])) {
                            array_push($filledArray, $arr[$i]);
                        } else {
                            if (array_search($value, $filledArray) === false) {
                                $arr[$i] = $value;
                            }
                            break;
                        }
                    }
                    break;
            }
        }
    }
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->save($a);
}

echo '<pre>';

echo '</pre>';