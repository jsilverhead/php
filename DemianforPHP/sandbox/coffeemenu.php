<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee menu</title>
    <style>
        body {
            font-family: "Oswald", sans-serif;
        }

        p {
            color: #fff;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        .label {
            color: #fff;
            display: block;
            position: relative;
            padding-left: 35px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 22px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            line-height: 25px;
        }

        .label:hover {
            cursor: pointer;
        }

        .label input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .pressed {
            position: absolute;
            top: 0;
            left: 0;
            height: 23px;
            width: 21px;
            background-color: transparent;
            border: 3px solid #ebe1e2;
        }

        .label input:checked~.pressed {
            background: #6a4546;
        }

        .pressed:after {
            content: "";
            position: absolute;
            display: none;
        }

        .label input:checked~.checkmark:after {
            display: block;
        }

        .label .pressed:after {
            left: 9px;
            top: 5px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }

        .menu {
            background: #817170;
            padding: 86px 30px 35px;
            max-width: 1980px;
            max-height: 900px;
        }

        .header {
            text-align: center;
        }

        .names {
            text-align: center;
            padding: 10px;
        }

        .orders {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 64px;
        }

        .column1 {
            width: 40%;
        }

        .column2 {
            width: 40%;
        }

        .numbers {
            max-width: 130px;
            max-height: 55px;
            font-family: "Oswald", sans-serif;
            color: #fff;
            border: 3px solid #fff;
            background: transparent;
            text-align: center;
        }

        .numbers::-webkit-inner-spin-button,
        .numbers::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }

        .order {
            border: 3px solid #fff;
            background-color: #6a4546;
        }

        .clientinfo {
            border: 3px solid #fff;
            background-color: transparent;
            text-align: center;
            font-family: "Oswald", sans-serif;
            color: #fff;
            width: 300px;
            height: 55px;
            font-style: normal;
            font-weight: 300;
            font-size: 20px;
            line-height: 30px;
            margin-left: 64px;
        }

        .clientinfo::placeholder {
            font-family: "Oswald", sans-serif;
            color: #fff;
            opacity: 90%;
            font-style: normal;
            font-weight: 300;
            font-size: 20px;
            line-height: 30px;
        }

        .results {
            display: flex;
            justify-content: center;
            align-content: center;
            text-align: center;
            flex-direction: column;
        }

        .btn {
            border: 3px solid #fff;
            background: #fff;
            font-family: "Oswald", sans-serif;
            color: #817170;
            max-width: 190px;
            font-style: normal;
            font-weight: 500;
            font-size: 20px;
            line-height: 30px;
            text-align: center;
            text-transform: uppercase;
            align-self: center;
        }

        .btn:hover {
            cursor: pointer;
            background: #817170;
            color: #fff
        }

        .row {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <header>
        <div class="header">
            <h1>COFFEHOUSE</h1>
        </div>
    </header>
    <main>
        <div class="menu">
            <div class="names">
                <input type="text" name="firstname" id="firstname" placeholder="Имя" class="clientinfo"> <input
                    type="text" name="lastname" id="lastname" placeholder="Фамилия" class="clientinfo">
            </div>
            <div class="orders">
                <div class="column1">
                    <div class="row"><label class="label">Эспрессо 80р.<input type="checkbox" name="order" id="1order"
                                onchange="<? $espresso->checkOne($value) ?>"><span class="pressed"></span></label>
                        <input type="number" name="price" id="order1-num" class="numbers" min="0" value="0"
                            data-check="1" onchange="<? $espresso->__set($name = 'price', $value) ?>">
                    </div><br>
                    <div class="row"><label class="label">Американо 110р.<input type="checkbox" name="order"
                                onchange="<? $espresso->checkOne($value) ?>" id="2order"><span
                                class="pressed"></span></label> <input type="number" name="order2-num" id="order2-num"
                            class="numbers" min="0" value="0" data-check="2"></div><br>
                    <div class="row"><label class="label">Латте 120р.<input type="checkbox" name="order"
                                onchange="<? $espresso->checkOne($value) ?>" id="3order"><span
                                class="pressed"></span></label> <input type="number" name="order3-num" id="order3-num"
                            class="numbers" min="0" value="0" data-check="3"></div><br>
                    <div class="row"><label class="label">Капучино 90р.<input type="checkbox" name="order"
                                onchange="<? $espresso->checkOne($value) ?>" id="4order"><span
                                class="pressed"></span></label> <input type="number" name="order4-num" id="order4-num"
                            class="numbers" min="0" value="0" data-check="4"></div><br>
                </div>
                <div class="column2">
                    <div class="row"><label class="label">Шоколадный кекс 80р.<input type="checkbox" name="order"
                                onchange="<? $espresso->checkOne($value) ?>" id="5order"><span
                                class="pressed"></span></label> <input type="number" name="order4-num" id="order5-num"
                            class="numbers" min="0" value="0" data-check="5"></div>
                    <br>
                    <div class="row"><label class="label">Черничный кекс 90р.<input type="checkbox" name="order"
                                onchange="<? $espresso->checkOne($value) ?>" id="6order"><span
                                class="pressed"></span></label> <input type="number" name="order5-num" id="order6-num"
                            class="numbers" min="0" value="0" data-check="6"></div>
                    <br>
                    <div class="row"><label class="label">Яблочный тарт 100р.<input type="checkbox" name="order"
                                onchange="<? $espresso->checkOne($value) ?>" id="7order"><span
                                class="pressed"></span></label> <input type="number" name="order6-num" id="order7-num"
                            class="numbers" min="0" value="0" data-check="7"></div>
                    <br>
                </div>
            </div>
            <div class="results">
                <p>Итого: <span id="result">0 р.</span></p>
                <button id="btn" class="btn">Оформить заказ</button>
            </div>
        </div>
    </main>
    <?php
    class Coffeemenu
    {
        protected string $name;
        protected int $price;
        private int $count;

        public function __construct($name, $price)
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
        public function checkOne($value)
        {
            if (true === $value) {
                $this->count = 1;
            } else {
                $this->count = 0;
            }
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

    $espresso = new Coffeemenu('espresso', 80);
    $americano = new Coffeemenu('americano', 110);
    $latte = new Coffeemenu('latte', 120);
    $capuccino = new Coffeemenu('capuccino', 90);
    $applePie = new Coffeemenu('apple pie', 200);
    $cherryPie = new Coffeemenu('cherry pie', 220);
    $brownie = new Coffeemenu('brownie', 150);
    $tip = new Coffeemenu('tip', 10);
    ?>
</body>

</html>