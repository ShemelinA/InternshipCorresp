<?php
/*

convertString($a, $b). Результат ее выполнение: если в строке $a содержится 2 и более подстроки $b,
то во втором месте заменить подстроку $b на инвертированную подстроку.

mySortForKey($a, $b). $a – двумерный массив вида [['a'=>2,'b'=>1],['a'=>1,'b'=>3]], $b – ключ вложенного массива.
Результат ее выполнения: двумерном массива $a отсортированный по возрастанию значений для ключа $b.
В случае отсутствия ключа $b в одном из вложенных массивов, выбросить ошибку класса Exception с индексом неправильного массива.

*/
function convertString($a, $b)
{
    $result = $a;
    $pos = strpos($a, $b);
    if ($pos)
    {
        $tempString = substr($a, $pos + 1);
        $pos2 = strpos($tempString, $b);
        if ($pos2)
        {
            $part1 = substr($a, 0, $pos + 1);
            $part2 = substr_replace($tempString, "===", $pos2, strlen($b));
            $result = $part1 . $part2;
        }
    }
    return $result;
}
echo convertString("1234545456789", "454");