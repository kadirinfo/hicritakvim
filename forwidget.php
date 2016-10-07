<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hicri Takvim Dönüştürücü</title>
    <meta name="description" content="Hicri takvimi miladiye, miladi takvimi hicriye dönüştürür.">

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            width: auto;
            max-width: 680px;
            padding: 0 15px;
        }
    </style>

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
    <?php

    include_once("hicri.php");
    include_once("functions.php");

    use hicritakvim\hicri;

    $date['day'] = filter_input(INPUT_GET, 'day', FILTER_SANITIZE_NUMBER_INT);
    $date['month'] = filter_input(INPUT_GET, 'month', FILTER_SANITIZE_NUMBER_INT);
    $date['year'] = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_NUMBER_INT);

    if (isset($_REQUEST['convert']) && in_array($_REQUEST['convert'], ['miladi', 'hicri'])) {
        $convert = $_REQUEST['convert'];
    } else
        $convert = false;

    echo '<blockquote>';

    if ($convert == 'hicri') { //todo: alt satırdaki mktime düzeltilecek
        $dateMiladi = parseDateStr(date('Y-m-d', mktime(0, 0, 0, $date['month'], $date['day'], $date['year'])));
        $dateHicri = hicri::miladiHicri($dateMiladi);

        $tarihGeceliMi = $date['month'] == $dateMiladi['month'] && $date['day'] == $dateMiladi['day'] && $date['year'] == $dateMiladi['year'];

        if (!$tarihGeceliMi) {
            showError('Hatalı tarih girildi - ' . $dateMiladi['day'] . ' ' . hicri::$miladiMonth[$dateMiladi['month']] . ' ' . $dateMiladi['year'] . ' olarak düzeltildi');

        }

        if ($dateHicri === false) {
            showError('dönüşüm hatası oluştu');
        } elseif (isset($dateHicri['error'])) {
            showError($dateHicri['error']);
            $dateHicri = $dateHicri['date'];

        } else {
            echo '<h3>' . hicri::hicriStr($dateHicri['date']) . ' ' . $dateHicri['dow'] . '</h3>';

            if (true===$dateHicri['hilal']  && mktime(0, 0, 0, $dateMiladi['month'], $dateMiladi['day'], $dateMiladi['year']) > mktime(0, 0, 0, 11, 30, 1978)) {
                if (mktime(0, 0, 0, $dateMiladi['month'], $dateMiladi['day'], $dateMiladi['year']) < time())
                    echo '<footer>hilal görünümü dikkate alınmıştır </footer>';
                elseif (mktime(0, 0, 0, $dateMiladi['month'], $dateMiladi['day'], $dateMiladi['year']) < mktime(0, 0, 0, 1, 22, 2023))
                    echo '<footer>astronomik hilal hesaplaması yapılmıştır </footer>';
                else
                    echo '<footer>matematiksel hesap yapılmıştır</footer>';
            } else
                echo '<footer>matematiksel hesap yapılmıştır</footer>';

            $dateHicri = $dateHicri['date'];
        }
    } elseif ($convert == 'miladi') {
        $dateHicri = $date;
        $dateMiladi = hicri::hicriMiladi($date);

        if ($dateMiladi === false) {
            showError('dönüşüm hatası oluştu');

        } elseif (isset($dateMiladi['error'])) {
            showError($dateMiladi['error']);
            $dateMiladi = parseDateStr(date('Y-m-d', time()));
        } else {
            echo '<h3>' . hicri::miladiStr($dateMiladi['date']) . ' ' . $dateMiladi['dow'] . '</h3>';

            if ($dateMiladi['hilal']===true) {

                if (mktime(0, 0, 0, $dateMiladi['date']['month'], $dateMiladi['date']['day'], $dateMiladi['date']['year']) < time() && mktime(0, 0, 0, $dateMiladi['date']['month'], $dateMiladi['date']['day'], $dateMiladi['date']['year']) > mktime(0, 0, 0, 11, 30, 1978))
                    echo '<footer>hilal görünümü dikkate alınmıştır</footer>';

                elseif (mktime(0, 0, 0, $dateMiladi['date']['month'], $dateMiladi['date']['day'], $dateMiladi['date']['year']) < mktime(0, 0, 0, 1, 22, 2023))
                    echo '<footer>astronomik hilal hesaplaması yapılmıştır</footer>';
                else
                    echo '<footer>matematiksel hesap yapılmıştır</footer>';
            } else
                echo '<footer>matematiksel hesap yapılmıştır</footer>';

            $dateMiladi = $dateMiladi['date'];
        }

    } else {
        $dateHicri = hicri::miladiHicri(date('Y-m-d', time()), 'array');
        $dateMiladi = parseDateStr(date('Y-m-d', time()));
        ?><p class="lead">Hicri Takvim dönüştürmek için aşağıdaki alanlardan istediğiniz tarihi seçip sonra "Çevir"e
            tıklayın</p><?php
    }
    echo '</blockquote>';
    ?>

    <form class="form-horizontal" method="get">
        <input type="hidden" name="convert" value="miladi"/>
        <fieldset>
            <legend>Hicri'den Miladiye</legend>
            <div class="form-group">
                <div class="col-md-2">
                    <select id="dayhicri" name="day" class="form-control">
                        <?php
                        echo range2option(1, 30, $dateHicri['day']);
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="monthhicri" name="month" class="form-control">
                        <?php
                        echo array2option(hicri::$hicriMonth, $dateHicri['month']);
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="yearhicri" name="year" class="form-control">
                        <?php
                        echo range2optionDesc(1472, 1200, $dateHicri['year']);
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="h2m" class="btn btn-primary">Çevir</button>
                </div>
            </div>
        </fieldset>
    </form>
    <form class="form-horizontal" method="get">
        <input type="hidden" name="convert" value="hicri"/>
        <fieldset>
            <legend>Miladi'den Hicriye</legend>
            <div class="form-group">
                <div class="col-md-2">
                    <select id="daymiladi" name="day" class="form-control">
                        <?php
                        echo range2option(1, 31, $dateMiladi['day']);
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="monthmiladi" name="month" class="form-control">
                        <?php
                        echo array2option(hicri::$miladiMonth, $dateMiladi['month']);
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="yearmiladi" name="year" class="form-control">
                        <?php
                        echo range2optionDesc(2050, 622, $dateMiladi['year']);
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="m2h" class="btn btn-primary">Çevir</button>

                </div>
            </div>

        </fieldset>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

<script>
    var yearmiladi=$('#yearmiladi').val();
    var monthmiladi=$('#monthmiladi').val();
    var daymiladi=$('#daymiladi').val();

    
    var yearhicri=$('#yearhicri').val();
    var monthhicri=$('#monthhicri').val();
    var dayhicri=$('#dayhicri').val();

    $('#m2h').click(function() {
        if(yearmiladi<=622 && monthmiladi<7  ){
            alert('Temmuz 622den önceki miladi tarihleri hesaplayamıyoruz.');
            return false;
        }     

    });


    $('#h2m').click(function() {
        if(yearhicri<1 && monthhicri<1 && dayhicri<1 ){
            alert('Hicri 1-1-1 tarihinden önceki tarihleri hesaplayamıyoruz.');
            return false;
        }     

    });


   



</script>

</body>
</html>