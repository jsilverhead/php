<?php
class Coffeemenu
{
    protected string $name;
    protected int $price;
    private int $count;

    protected function __construct($name, $price)
    {
        $this->name = strtoupper($name);
        $this->price = $price;
    }

    public function __set($name, $value)
    {

    }
    public function __get($name)
    {
        echo $name;
    }
}

class countItems
{
    static array $finalPrice;
    static int $overallprice;

    static function countItems($price, $items)
    {
        $sum = $price * $items;
        array_push(self::$finalPrice, $sum);
    }
    static function overallSum()
    {
        self::$overallprice = array_sum(self::$finalPrice);
    }
}

$coffee = new Coffeemenu('coffee', 150);
$applePie = new Coffeemenu('apple pie', 200);
$cherryPie = new Coffeemenu('cherry pie', 220);
$brownie = new Coffeemenu('brownie', 150);
$tip = new Coffeemenu('tip', 10);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee menu</title>
</head>

<body>

</body>

</html>