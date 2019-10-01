<?php
namespace Test3;

/*use Exception;*/
class newBase
{
    static private $count = 0;
    static private $arSetName = [];
    /**
     * @param string $name
     */
    function __construct(int $name = 0)
    /*function __construct(string $name = null)*/
    {
        if (empty($name)) {
            while (array_search(self::$count, self::$arSetName) != false) {
                ++self::$count;
            }
            $name = /*(string)*/self::$count;
        }
        $this->name = $name;
        self::$arSetName[] = $this->name;
    }
    private $name;
    /**
     * @return string
     */
    public function getName(): string
    {
        return '*' . $this->name  . '*';
        /*return $this->name;*/
    }
    protected $value;
    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    // @return int
    /**
     * @return string
     */
    public function getSize()
    {
        $size = strlen(serialize($this->value));
        return strlen($size) + $size;
        /*$size = $this->value ? strlen(serialize($this->value)) : 0;
        return $size;*/
    }
    public function __sleep()
    {
        return [/*'name', */'value'];
    }
    /**
     * @return string
     */
    public function getSave(): string
    {
        $value = serialize($value);
        return $this->name . ':' . sizeof($value) . ':' . $value;
        /*$value = serialize($this->value);
        return $this->name . ':' . sizeof((array)$this->value) . ':' . $value;*/
    }
    //@param string $value
    /**
     * @return newBase
     */
    static public function load(string $value): newBase
    /*static public function load(string $value)*/
    {
        $arValue = explode(':', $value);
        return (new newBase($arValue[0]))
            ->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
                + strlen($arValue[1]) + 1), $arValue[1]));
        /*$result = new newBase($arValue[0]);
        $result->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
            + strlen($arValue[1]) + 1)));
        return $result;*/
    }
}
class newView extends newBase
{
    private $type = null;
    private $size = 0;
    private $property = null;
    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->setType();
        $this->setSize();
    }
    public function setProperty($value/*$property*/)
    {
        $this->property = $value;
        return $this;
//        $this->property = $property;
    }
    private function setType()
    {
        $this->type = gettype($this->value);
    }
    private function setSize()
    {
        if (is_subclass_of($this->value, "Test3\newView")) {
//        if (is_subclass_of($this->value, "Test3\\newView")) {
            $this->size = parent::getSize() + 1 + strlen($this->property);
        } elseif ($this->type == 'test') {
            $this->size = parent::getSize();
        } else {
            $this->size = strlen($this->value);
//            $this->size = strlen($this->property);
        }
    }
    // @return array
    /**
     * @return string
     */
    public function __sleep()
    {
        return [/*'type', 'size', */'property'];
    }
    // @throws Exception
    /**
     * @return string
     */
    public function getName(): string
    {
        if (empty($this->name)) {
//        if (empty(parent::getName())) {
            throw new Exception('The object doesn\'t have name');
        }
        return '"' . $this->name  . '": ';
//        return '"' . parent::getName()  . '";';
    }
    /**
     * @return string
     */
    public function getType(): string
    {
        return ' type ' . $this->type  . ';';
    }
    /**
     * @return string
     */
    public function getSize(): string
    {
        return ' size ' . $this->size . ';';
    }
    public function getInfo()
    {
        try {
            echo $this->getName()
                . $this->getType()
                . $this->getSize()
                . "\r\n";
        } catch (Exception $exc) {
            echo 'Error: ' . $exc->getMessage();
        }
    }
    /**
     * @return string
     */
    public function getSave(): string
    {
        if ($this->type == 'test') {
            $this->value = $this->value->getSave();
        }
        return parent::getSave() . serialize($this->property);
        // не понятно что должен делать if - на мой взгляд его целиком надо вырезать
    }
    // @param string $value
    /**
     * @return newView
     */
    static public function load(string $value): newBase
//    static public function load(string $value = '')
    {
        $arValue = explode(':', $value);
        return (new newBase($arValue[0]))
            ->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
                + strlen($arValue[1]) + 1), $arValue[1]))
            ->setProperty(unserialize(substr($value, strlen($arValue[0]) + 1
                + strlen($arValue[1]) + 1 + $arValue[1])))
            ;
        /*$result = new newView($arValue[0]);
        $result->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
            + strlen($arValue[1]) + 1)));
        $result->setProperty(unserialize(substr($value, strrpos($value, '}') + 1)));
        return $result;*/
    }
}
function gettype($value): string
{
    if (is_object($value)) {
        $type = get_class($value);
        do {
            if (strpos($type, "Test3\newBase") !== false) {
                return 'test';
            }
        } while ($type = get_parent_class($type));
    }
    return gettype($value);
}
/*function gettype($value): string
{
    $result = null;
    if (is_object($value)) {
        $type = get_class($value);
        $parType = get_parent_class($type);
        do {
            if (strpos($type, "Test3\\newBase") !== false) {
                $result = 'test';
                break;
            }
        } while ($parType && $type = $parType);
        $result = $result ? $result : $type;
    }
    return $result;
}*/


$obj = new newBase('12345');
$obj->setValue('text');

$obj2 = new \Test3\newView('O9876');
$obj2->setValue($obj);
$obj2->setProperty('field');
$obj2->getInfo();

$save = $obj2->getSave();

$obj3 = newView::load($save);

var_dump($obj2->getSave() == $obj3->getSave());

