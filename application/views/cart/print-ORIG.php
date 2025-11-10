<head>
    <title>print</title>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet"
          href="<?php echo asset_url() ?>others/Font-Awesome-Pro/web-fonts-with-css/css/fontawesome-all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
    <link rel='stylesheet' href="<?php echo asset_url() ?>css/print_style_memotags.css?v=<?php echo rand() ?>">
</head>

<div id='filters' class='row do-not-print'>
    <div class='col-12'>
        <ul id='filters' class="list-group list-group-flush">
            <li class="list-group-item">
                <b>Printing Options</b>
            </li>
            <li class="list-group-item">
                Memotag Top Margin
                <input id="class-update-top-margin" type="number" min="0" max="10" value="4.5" step="0.1" class="inputUpdateClass" data-target=".body" data-style="margin-top" data-metric="cm">
            </li>
            <li class="list-group-item">
                Memotag Squeeze
                <input id="class-update-squeeze" type="number" min="0" max="10" value="8.0" step="0.1" class="inputUpdateClass" data-target="dd.col-4.pl-0" data-style="margin-bottom" data-metric="px">
            </li>
            <li class="list-group-item">
                Squeeze
                <i class="fa fa-toggle-on btnToggleView pull-right" data-toggle-key='squeeze'></i>
            </li>
            <li class="list-group-item">
                Include image
                <i class="fa fa-toggle-on btnToggleView pull-right" data-toggle-key='img'></i>
            </li>
            <li class="list-group-item">
                Total colorways (#)
                <i class="fa fa-toggle-on btnToggleView pull-right" data-toggle-key='total_aval_colors'></i>
            </li>
            <li class="list-group-item">
                Additional colorways
                <i class="fa fa-toggle-on btnToggleView pull-right" data-toggle-key='additional_aval_colors'></i>
            </li>
            <li class="list-group-item">
                Backing
                <i class="fa fa-toggle-on btnToggleView pull-right" data-toggle-key='backing'></i>
            </li>
            <li class="list-group-item">
                Finishing<br><small>(`Stain` and `Black out` only)</small>
                <i class="fa fa-toggle-on btnToggleView pull-right" data-toggle-key='finish'></i>
            </li>
            <li class="list-group-item">
                Cleaning<br><small>(`Bleach cleanable` only)</small>
                <i class="fa fa-toggle-on btnToggleView pull-right" data-toggle-key='cleaning'></i>
            </li>
            <li class='list-group-item d-flex justify-content-around'>
                <i class="far fa-print fa-2x" data-toggle='tooltip' data-title='Print'
                   onclick='javascript: window.print()'></i>
                <i id='btnExcelExport' class="far fa-file-spreadsheet fa-2x" data-toggle="tooltip"
                   data-title="Excelsheet export"></i>
            </li>

            <?php
                $numOfCustomFields = 2;
                for($k = 1; $k <= $numOfCustomFields; $k++){
            ?>
                <li class="list-group-item">
                    Custom Field <?php echo$k?>
                    <ul class="list-group list-group-flush">
                        <li>
                            <div class="row">
<!--                                <div class="col-2">-->
<!--                                    Name-->
<!--                                </div>-->
                                <div class="col-12">
                                    <input id="inputCustomFieldKey_<?php echo$k?>" type="text" class="inputCustomFieldKey" data-target="<?php echo$k?>">
<!--                                    <i class="fa fa-toggle-on btnCustomFieldTopBottom pull-right" data-toggle-key='inputCustomFieldKey_--><?php //=$k?><!--'></i>-->
                                </div>
                            </div>
                        </li>
<!--                        <li>-->
<!--                            <div class="row">-->
<!--                                <div class="col-2">-->
<!--                                    Value-->
<!--                                </div>-->
<!--                                <div class="col">-->
<!--                                    <input id="inputCustomFieldValue_--><?php //=$k?><!--" type="text" class="inputCustomFieldValue" data-target="--><?php //=$k?><!--">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </li>-->
                    </ul>
                </li>
            <?php
                }
            ?>
            <li>
                Count: <?php echocount($collectionToShow);?>
            </li>
        </ul>
    </div>
</div>

<?php
$column1 = 'col-2';
$column2 = 'col-4 pl-0';
$column3 = 'col-6'; // Filler on the right of the memotag

$column1Custom = "";
//echo $htmlTable; exit;
//echo "<pre>"; var_dump(json_encode($collectionRows));exit;
//echo "<pre>"; var_dump($collectionToShow);exit;

$i = 0;
foreach ($collectionToShow as $row) {
    //var_dump($row);
    ?>
    <body>
    <div class='body' style="<?php echo ($i < count($collectionToShow) - 1 ? 'page-break-after: always;' : '') ?>">

        <?php
//        echo $i;
        // Some general defs

        $txt = '';
        if ($row['outdoor']) {
            $txt = "<p class='mb-2' style='font-size:1.2em'><b>INDOOR/OUTDOOR</b></p>";
        }

        if ($row['product_type'] === constant('Digital')) {
            $isDigital = true;
            $explode_name = explode(' / ', $row['Pattern']);
            $row['Pattern'] = array_shift($explode_name);
            $row['Color'] = implode($explode_name, ' / ') . ' / ' . str_replace('/', '', $row['Color']);
        } else {
            $isDigital = false;
        }

        if (!empty($txt)) {
            ?>
            <div class='row'>
                <div class='col-12'>
                    <?php echo trim($txt) ?>
                </div>
            </div>
            <?php
        }
        ?>
        <div style='height:280px; max-width: 87%;'>
            <dl class='row' style='line-height: 19px;	'>

                <?php
                    for($j = 1; $j <= $numOfCustomFields; $j++){
                ?>
                        <dd class="col-12 m-0 <?php echo$column1Custom?>">
                            <b class="customFieldKey_<?php echo$j?>" style="font-size:1.6em;"></b>
                        </dd>
<!--                        <dd id="customFieldValue_--><?php //=$j?><!--" class="--><?php //=$column1Custom?><!--"></dd>-->
                        <div class="<?php echo$column1Custom?>"></div>
                <?php
                    }
                ?>

                <?php
                // Key indexes to skip
                $to_skip = array('picture', 'total_aval_colors', 'product_type', 'outdoor', 'status');
                //var_dump($row);exit;
                foreach ($row as $key => $value) {
                    if (!in_array($key, $to_skip)) {
                        if (!is_array($value)) {
                            // Single value
                            ?>
                            <?php if ($key === 'Item #') { ?>
                                <dd class="col-12 mb-2">
                                    <b class='font-bold' style='font-size:1.6em;'><?php echo $value ?></b>
                                </dd>
                                <dd class="hide"></dd>
                            <?php } else if ($key === 'Pattern') { ?>
                                <dt class="<?php echo $column1 ?>"><?php echo $key ?>:</dt>
                                <dd class="<?php echo $column2 ?>"><b style='font-size:1.2em'><?php echo $value ?></b></dd>
                                <div class='<?php echo $column3 ?>'></div>
                            <?php } else if ($key == 'qr') { ?>
                                <dd class="col-12"><?php echo $value ?></dd>
                            <?php } else { ?>
                                <dt class="<?php echo $column1 ?>"><?php echo $key ?>:</dt>
                                <dd class="<?php echo $column2 ?>"><?php echo $value ?></dd>
                                <div class='<?php echo $column3 ?>'></div>
                            <?php } ?>

                            <?php
                        } else {
                            // Multiple lines

                            if ($key === 'Fire Rating') {

                                ?>
                                <dd class="col-6 b-small">
                                    <?php
                                    $n = 0;
                                    $fc = array();
                                    $tobetreated = array();
                                    foreach ($value as $c) {
                                        $n++;
                                        $is_to_be_treated = strpos($c, 'Can be treated') !== false;
                                        if (!$is_to_be_treated) {
                                            array_push($fc, "<span class='b'>" . trim($c) . "</span>");
                                        } else {
                                            array_push($tobetreated, "<span class='b'>" . trim($c) . "</span>");
                                        }

                                        //echo "<span class='b'>" . trim($c) . ( $n < count($value) ? ",&nbsp;" : '' ) . "</span>" ;
                                    }
                                    if (!empty($fc)) {
                                        echo "Passes " . implode(',&nbsp;', $fc);
                                    }
                                    if (!empty($tobetreated)) {
                                        echo (!empty($fc) ? "<br>" : '') . implode(',&nbsp;', $tobetreated);
                                    }

                                    ?>
                                </dd>
                                <div class='<?php echo $column3 ?>'></div>
                                <?php

                            } else {

                                ?>
                                <dt class="<?php echo $column1 ?>" data-key='<?php echo url_title($key, null, true) ?>'><?php echo $key ?>:
                                </dt>
                                <dd class="<?php echo $column2 ?>"
                                    data-key='<?php echo url_title($key, null, true) ?>'><?php echo "<span class='b'>" . implode(", </span><span class='b'>", $value) . "</span>"; ?></dd>
                                <div class='<?php echo $column3 ?>' data-key='<?php echo url_title($key, null, true) ?>'></div>
                                <?php

                            }
                        }
                    }
                }
                ?>

                <?php
                if (isset($row['total_aval_colors'])) {
                    ?>
                    <dd class="col-12 mb-2" data-key='total_aval_colors'>
                        <b class='font-bold'><?php echo $row['total_aval_colors'] ?></b>
                    </dd>
                    <?php
                }
                ?>

                <dd class="col-12 mb-2" data-key='additional_aval_colors'>
                    <b class='font-bold'>AVAILABLE IN ADDITIONAL COLORWAYS</b>
                </dd>

            </dl>
        </div>

        <?php if (!is_null($row['picture'])) {

// 			$marginTop = 88;
            $marginTop = 55;

            if (!empty($txt)) {
                $marginTop -= 40;
            }

            ?>

            <div class='img-container' style='margin-top: <?php echo $marginTop ?>px;' data-key='img'>
                <img style='' src='<?php echo site_url() . $row['picture'] ?>'>
            </div>

        <?php } ?>

    </div>
    </body>

    <?php
    $i = $i + 1;
}
?>

<foot>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
            integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
            crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
            integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
            crossorigin="anonymous"></script>
</foot>
<script>
    var print_type = '<?php echo$print_type?>';

    $(".inputCustomFieldKey, .inputCustomFieldValue").on("change", function () {
        var customFieldTargetNum = $(this).attr('data-target');
        var inputKeyId = "#inputCustomFieldKey_" + customFieldTargetNum;
        // var inputValId = "#inputCustomFieldValue_" + customFieldTargetNum;

        var key = $(inputKeyId).val();
        // var value = $(inputValId).val();
        // console.log(key)

        var targetKeyId = "dd > b.customFieldKey_" + customFieldTargetNum;
        // var targetValId = "#customFieldValue_" + customFieldTargetNum;

        // console.log(targetKeyId)

        $(targetKeyId).html(key);
        // $(targetValId).html(value);
    })

    $("#filters").on('click', '.btnToggleView', function () {
        var k = $(this).attr('data-toggle-key')
        if (k == 'squeeze') {
            var is_on = $(this).hasClass('fa-toggle-on')
            if (is_on) {
                $('dd.col-4.pl-0').css('margin-bottom', '5px')
                $("#class-update-squeeze").val("5.0");
            } else {
                $('dd.col-4.pl-0').css('margin-bottom', '8px')
                $("#class-update-squeeze").val("8.0");
            }
        }
        toggle_views($(this));
    })

    $("#filters").on("change", "input.inputUpdateClass", function(){
        // <input type="number" min="1" max="10" value="6.2" class="inputUpdateClass" data-target=".body" data-style="margin-top" data-metric="cm">
        let targetElements = $(this).attr("data-target");
        let targetStyle = $(this).attr("data-style");
        let targetStyleValue = $(this).val();
        let targetStyleValueMetric = $(this).attr("data-metric");
        // console.log(targetElements, targetStyle, targetStyleValue, targetStyleValueMetric);
        $(targetElements).css(targetStyle, targetStyleValue+targetStyleValueMetric);
    })

    function toggle_views(obj, classes_to_toggle = ['fa-toggle-on', 'fa-toggle-off']) {
        let key = obj.attr('data-toggle-key');
        $("[data-key='" + key + "']").toggleClass('hide');
        $.each(classes_to_toggle, function (index, value) {
            obj.toggleClass(value);
        });
    }

    $(document).ready(function () {
        if (print_type === 'digital_ground') {
            toggle_views($(".btnToggleView[data-toggle-key='img']"));
            toggle_views($(".btnToggleView[data-toggle-key='total_aval_colors']"));
        }
        // Init Backing as off
        toggle_views($(".btnToggleView[data-toggle-key='backing']"));
        // toggle_views( $(".btnToggleView[data-toggle-key='finish']") );
        // toggle_views( $(".btnToggleView[data-toggle-key='cleaning']") );
        toggle_views($(".btnToggleView[data-toggle-key='additional_aval_colors']"));
        if ($('[data-toggle="tooltip"]').length > 0) $('[data-toggle="tooltip"]').tooltip({
            boundary: 'viewport',
            container: 'body',
            template: '<div class="tooltip do-not-print" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
        });
    });

    $(document).on('click', '#btnExcelExport', function () {
        let json = String(`<?php echojson_encode($collectionRows)?>`);
        //console.log(JSON.parse(json));
        DownloadJSON2CSV(json);
    })

    function DownloadJSON2CSV(objArray) {
        var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
        let s = '';
        var str = '';

        // Heading
        str += 'Item #,Pattern,Color,Width,Repeat,Content,Back Content,Abrasion,Firecodes,Additional Colorways,\r\n';

        for (var i = 0; i < array.length; i++) {
            var line = '';

            for (var index in array[i]) {
                s = array[i][index].replace(/&quot;/gi, '""').replace(/&#44;/gi, ',').replace(/&nbsp;/gi, '');
                line += '"' + s + '",';
            }

            // Here is an example where you would wrap the values in double quotes
            // for (var index in array[i]) {
            //    line += '"' + array[i][index] + '",';
            // }

            line.slice(0, line.Length - 1);

            str += line + '\r\n';
        }
        let downloadLink = document.createElement("a");
        downloadLink.href = "data:text/csv;charset=utf-8," + escape(str);
        downloadLink.download = "memotags.csv";

        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        //window.open();
    }
</script>
</html>