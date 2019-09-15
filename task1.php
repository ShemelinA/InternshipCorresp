<?php
function is_prime ($n)
{
    for($x = 2; $x <= sqrt($n); $x++) {
        if($n % $x == 0) {
            return false;
        }
    }
    return true;
}

function findSimple ($a, $b) {
	if (!is_int($a) || !is_int($b) || $a > $b || $a < 0) {
		return "Some error!";
	}

	for($i = $a; $i <= $b; $i++) {
        if(is_prime($i)) {
            $arr[] = $i;
        }
    }
	return $arr;
}

function createTrapeze ($a) {
	if (gettype($a) != "array" || count($a) % 3 != 0) {
		return "Some error!";
	}
	$arr = [];

	for($i = 0; $i < count($a) / 3; $i++) {
        $arr[] = [
        	'a' => $a[$i * 3],
        	'b' => $a[$i * 3 + 1],
        	'c' => $a[$i * 3 + 2]
        ];
    }
	return $arr;
}

function squareTrapeze($a) {
	if (gettype($a) != "array") {
		return "Some error!";
	}
	foreach ($a as &$value) {
		$s = ($value['a'] + $value['b']) * $value['c'] / 2;
		$value += ['s' => $s];
	}

	return $a;
}

function getSizeForLimit($a, $b) {
	if (gettype($a) != "array") {
		return "Some error!";
	}

	$arr = [];
	foreach ($a as $key => $value) {
		if ($value['s'] <= $b) {
			$arr[$key] = array_splice($value, 0, -1);
		}
	}

	return max($arr);
}

function getMin($a) {
	if (gettype($a) != "array") {
		return "Some error!";
	}

	$min = array_slice($a, 0, 1);;
	foreach ($a as $value) {
		$min = $value < $min ? $value : $min;
	}

	return $min;
}

function printTrapeze($a) {
	if (gettype($a) != "array") {
		return "Some error!";
	}

	$table = "<table border='1'>";
	$table .= "<tr style='font-size: x-large;'>
		<th>a</th>
		<th>b</th>
		<th>c</th>
		<th>s</th>
	</tr>";

	foreach ($a as $value) {
		// Округлил значения площади для определения четности
		$style = round($value['s']) % 2 == 0 ? "" : "color:white;background-color:green;";
		$table .= "<tr style=".$style.">
			<td>".$value['a']."</td>
			<td>".$value['b']."</td>
			<td>".$value['c']."</td>
			<td>".round($value['s'])."</td>
		</tr>";
	}
	// Вывел столбец с s для более простого понимания

	return $table;
}
abstract class BaseMath {
	public function exp1($a, $b, $c) {
        return $a * ($b ^ $c);
    }

    public function exp2($a, $b, $c) {
        return ($a / $b) ^ $c;
    }
    public abstract function getValue();
}

class F1 extends BaseMath {

	var $a, $b, $c;

	function  __construct($a, $b, $c) {
		$this -> a = $a;
		$this -> b = $b;
		$this -> c = $c;
	}

    public function getValue() {
    	$val1 = BaseMath::exp1($this->a, $this->b, $this->c);
    	$val2 = BaseMath::exp2($this->a, $this->c, $this->b);
    	$val3 = min($this->a, $this->b, $this->c);
    	return ($val1 + ($val2 % 3) ^ $val3);
    }
}

$simpleArray = findSimple(1, 20);
$cTrapeze = createTrapeze(range(1,12));
$sTrapeze = squareTrapeze($cTrapeze);
$sizesTrapeze = getSizeForLimit($sTrapeze, 50);
$getMinResult = getMin([1,2,6,9,5,"56l"=>-50]);
$tableTrapeze = printTrapeze($sTrapeze);
$class = new F1(2,1,3);
$valueF1 = $class->getValue();
echo '<pre>';
var_dump($simpleArray);
echo '<br/><br/>';
var_dump($cTrapeze);
echo '<br/><br/>';
var_dump($sTrapeze);
echo '<br/><br/>';
var_dump($sizesTrapeze);
echo '<br/><br/>';
var_dump($getMinResult);
echo '<br/><br/>';
var_dump($valueF1);
echo '<br/><br/>';
echo '</pre>';
echo $tableTrapeze;
?>