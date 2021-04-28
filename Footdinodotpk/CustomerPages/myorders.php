<?php
if (!isset($_SESSION)) {
    session_start();
}
$_SESSION['CURRENTLY_IN'] = $_SERVER['PHP_SELF'];
require('cust.top.inc.php');
$customerId = $_SESSION['USERNAME'];
$msg = '';
if (array_key_exists('submitfb', $_POST)) {
    $errors = 0;
    $sOID = get_safe_values($con, $_POST['orderIdFb']);
    $sFID='fb'.$sOID;
    $sPID = get_safe_values($con, $_POST['productIdFb']);
    $sBID = get_safe_values($con, $_POST['businessIdFb']);
    $sFeedback = get_safe_values($con, $_POST['givefb']);
    $sRating = get_safe_values($con, $_POST['ratingFb']);
    $time = date('d/M/y H:i');
    if ($sRating == '') {
        $msg = 'Could not submit feedback because rating was not given';
        $errors = 1;
    }
    if ($sFeedback == '') {
        $msg = 'Could not submit feedback because feedback was empty';
        $errors = 1;
    }
    if ($errors == 0) {
        $sql = "insert into
                    orderfeedback(feedbackId,orderId,customerId,businessId,productId,feedback,rating,time,visible)
                    values('$sFID','$sOID','$customerId','$sBID','$sPID','$sFeedback','$sRating','$time','yes')";
        mysqli_query($con, $sql);
        header('location:myorders.php');
    }
}

$count = getCount('customerId', $customerId, 'orders');
if ($count > 0) {
    $orders = getRowsOfSameEntry('customerId', $customerId, 'orders');
    $orderdetail = getRowsOfSameEntry('customerId', $customerId, 'orderdetail');
    $businessDetail = getDataWRTOtherTable('businessvendordetail', 'id', 'id',
        'orders', 'businessId', 'customerId', $customerId);
    $productDetail = getDataWRTOtherTable('productdetail', 'productId', 'productId',
        'orders', 'productId', 'customerId', $customerId);

    $ac = 0;
    $pa = 0;
    $activeOrders = [];
    $pastOrders = [];
    date_default_timezone_set('Asia/Karachi');
    $timeNow=gmdate("Y-m-d\TH:i",time()+18000);
    $timeNow=str_replace('T', ' ',$timeNow);
    $timeNow=str_replace('-', '/',$timeNow);
    for ($i = 0; $i < count($orders); $i++) {
        $allOrders[$i]['oid'] = $orderdetail[$i]["orderId"];
        $allOrders[$i]['pid'] = $productDetail[$i]["productId"];
        $allOrders[$i]['pname'] = $productDetail[$i]["productName"];
        $allOrders[$i]['qty'] = $orderdetail[$i]["productQuantity"];
        $allOrders[$i]['price'] = $orderdetail[$i]["productTotalPrice"];
        $allOrders[$i]['bid'] = $businessDetail[$i]["id"];
        $allOrders[$i]['fcname'] = $businessDetail[$i]["businessName"];
        $allOrders[$i]['fcowner'] = $businessDetail[$i]["vendorName"];
        $allOrders[$i]['fccontact'] = $businessDetail[$i]["contactno"];
        $allOrders[$i]['addtime'] = $orderdetail[$i]["orderAddedTime"];
        $allOrders[$i]['addedtime'] = $orderdetail[$i]["orderAddedTime"];
        $allOrders[$i]['deltime'] = $orderdetail[$i]["deliveryTime"];
        $allOrders[$i]['status'] = $orderdetail[$i]["orderStatus"];
        $allOrders[$i]['statusId'] = $orderdetail[$i]["orderStatusId"];
        $allOrders[$i]['updtime'] = $orderdetail[$i]["orderStatusUpdatedTime"];
        $allOrders[$i]['suptime'] = $orderdetail[$i]["orderStatusUpdatedTime"];
        if ($allOrders[$i]['statusId'] == 'w' || $allOrders[$i]['statusId'] == 'a') {
            $activeOrders[$ac] = $allOrders[$i];
            if ($activeOrders[$ac]['statusId'] == 'a' && $timeNow>$activeOrders[$ac]['deltime']){
                $activeOrders[$ac]['status']='Time up';
            }
            $ac++;
        }
        if ($allOrders[$i]['statusId'] == 'c' || $allOrders[$i]['statusId'] == 'x') {
            $pastOrders[$pa] = $allOrders[$i];
            $pa++;
        }
    }
    unset($allOrders, $ac, $pa);

    if (array_key_exists('a_sortby', $_GET)) {
        $sortby = $_GET['a_sortby'];
        if ($sortby == 'pname' || $sortby == 'price' || $sortby == 'qty' || $sortby == 'fcowner' ||
            $sortby == 'fcname' || $sortby == 'status' || $sortby=='addtime' || $sortby == 'updtime') {
            $activeOrders = change_value2key($activeOrders, $sortby);
            if(!array_key_exists('sort',$_SESSION) || $_SESSION['sort']=='asc' ) {
                ksort($activeOrders);
                $_SESSION['sort']='desc';
            }
            else if($_SESSION['sort']=='desc') {
                krsort($activeOrders);
                $_SESSION['sort']='asc';
            }
            $activeOrders = array_values($activeOrders);
        }
    }
    if (array_key_exists('p_sortby', $_GET)) {
        $sortby = $_GET['p_sortby'];
        if ($sortby == 'pname' || $sortby == 'price' || $sortby == 'qty' || $sortby == 'fcowner' ||
            $sortby == 'fcname' || $sortby == 'status' || $sortby=='addtime' || $sortby == 'updtime') {
            $pastOrders = change_value2key($pastOrders, $sortby);
            if(!array_key_exists('sort',$_SESSION) || $_SESSION['sort']=='asc' ) {
                ksort($pastOrders);
                $_SESSION['sort']='desc';
            }
            else if($_SESSION['sort']=='desc') {
                krsort($pastOrders);
                $_SESSION['sort']='asc';
            }
            $pastOrders = array_values($pastOrders);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | FooDino</title>
    <link rel="stylesheet" href="../site/bootstrap.min.css">
    <link rel="stylesheet" href="../site/style.css">
    <link rel="stylesheet" href="../site/css/font-awesome.min.css">

    <script src="../files/includehtml.js"></script>

</head>

<body>
<div include-html="customer_header.php"></div>

<script>
    includeHTML();
</script>

<?php
if ($count == 0) {
    echo '<div class="container">
    <div class="py-5 text-center">

        <h2>You did not place any order yet</h2>
        <a href="stovepage.php" color="cyan">Place Order Now</a>
    </div>';
    die();
}
?>
<br>
<h6 align="center" id="error_msg" style="visibility: visible"><?php echo $msg;?></h6>
<div>
    <section class="container">
        <legend>ACTIVE ORDER</legend>
        <div class="table-responsive">
            <table class="table">
                <table id="activeOrder" class="table">
                    <thead>
                    <tr>
                        <th scope="col">
                            <a href="?a_sortby=pname">
                                Product Name
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=qty">
                                Quantity
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=price">
                                Total Price
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=fcname">
                                Food Centre Name
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=fcowner">
                                Owner
                            </a>
                        </th>
                        <th scope="col">Contact</th>
                        <th scope="col">
                            <a href="?a_sortby=addtime">
                                Added Time
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=deltime">
                                Delivery Time
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=status">
                                Status
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=updtime">
                                Update Time
                            </a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    for ($i = 0; $i < count($activeOrders); $i++) {
                        echo
                            '<tr>
                        
                        <th scope="row" style="font-family:Cambria Math; font-size:18px; ">
                            <a href="detailsprod.php?prod=' . $activeOrders[$i]['pid'] . '" >
                            ' . ucfirst($activeOrders[$i]['pname']) . '
                            </a>
                        </th>
                        <td>' . $activeOrders[$i]['qty'] . '</td>
                        <td>' . $activeOrders[$i]['price'] . '</td>
                        <td style="font-family:Cambria Math; font-size:18px; ">
                            <a href="vendordetail.php?vend=' . $activeOrders[$i]['bid'] . '" > 
                            ' . ucfirst($activeOrders[$i]['fcname']) . '
                            </a>
                        </td>
                        <td>' . ucfirst($activeOrders[$i]['fcowner']) . '</td>
                        <td>' . $activeOrders[$i]['fccontact'] . '</td>
                        <td>' . $activeOrders[$i]['addedtime'] . '</td>
                        <td>' . $activeOrders[$i]['deltime'] . '</td>
                        <td>' . $activeOrders[$i]['status'] . '</td>
                        <td>' . $activeOrders[$i]['suptime'] . '</td>
                        <td>
                        <form method="post" action="action.php">
                        <input type="hidden" name="oid" value="' . $activeOrders[$i]['oid'] . '">
                        <input type="hidden" name="pid" value="' . $activeOrders[$i]['pid'] . '">
                        <input type="hidden" name="bid" value="' . $activeOrders[$i]['bid'] . '">
                        <input type="hidden" name="cid" value="' . $customerId . '">
                        <div class="row">';
                        if ($activeOrders[$i]['statusId'] == "w") {
                            echo '
                            <button name="order_prod_remove" class="btn btn-primary" style="width: 70; height: 30">Remove</button>';
                        }
                        elseif ($activeOrders[$i]['status'] == "Time up") {
                            echo '
                            <button name="order_prod_complete" class="btn btn-primary" style="width: 70; height: 30">Complete</button>';
                        }
                        echo '
                        </div>
                        </form>
                        </td>
                    </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </table>
        </div>
    </section>
    <hr>
    <section class="container">
        <legend>PAST ORDER</legend>
        <div class="table-responsive">
            <table class="table">
                <table id="pastOrder" class="table">
                    <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">
                            <a href="?p_sortby=pname">
                                Product Name
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?p_sortby=qty">
                                Quantity
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?p_sortby=price">
                                Total Price
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?p_sortby=fcname">
                                Food Centre Name
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?p_sortby=fcowner">
                                Owner
                            </a>
                        </th>
                        <th scope="col">Contact</th>
                        <th scope="col">
                            <a href="?p_sortby=addtime">
                                Added Time
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?a_sortby=deltime">
                                Delivery Time
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?p_sortby=status">
                                Status
                            </a>
                        </th>
                        <th scope="col">
                            <a href="?p_sortby=updtime">
                                Update Time
                            </a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    for ($i = 0;
                         $i < count($pastOrders);
                         $i++) {
                        echo
                            '<tr>

                        <td>' . strval(intval($i) + 1) . '</td>
                        <th scope="row" style="font-family:Cambria Math; font-size:18px; ">
                            <a href="detailsprod.php?prod=' . $pastOrders[$i]['pid'] . '" >
                            ' . ucfirst($pastOrders[$i]['pname']) . '
                            </a>
                        </th>
                        <td>' . $pastOrders[$i]['qty'] . '</td>
                        <td>' . $pastOrders[$i]['price'] . '</td>
                        <td style="font-family:Cambria Math; font-size:18px; ">
                            <a href="vendordetail.php?vend=' . $pastOrders[$i]['bid'] . '" > 
                            ' . ucfirst($pastOrders[$i]['fcname']) . '
                            </a>
                        </td>
                        <td>' . ucfirst($pastOrders[$i]['fcowner']) . '</td>
                        <td>' . $pastOrders[$i]['fccontact'] . '</td>
                        <td>' . $pastOrders[$i]['addedtime'] . '</td>
                        <td>' . $activeOrders[$i]['deltime'] . '</td>
                        <td>' . $pastOrders[$i]['status'] . '</td>
                        <td>' . $pastOrders[$i]['suptime'] . '</td><td>';
                        $oid = $pastOrders[$i]['oid'];
                        if ($pastOrders[$i]['statusId'] == 'c') {
                            if (isContain('orderId', $oid, 'orderfeedback') == 0) {
                                echo '<button 
                                onclick="
                                document.getElementById(\'givefb\').setAttribute(\'placeholder\',
                                \'Order no. ' . strval(intval($i) + 1) . ' (' . ucfirst($pastOrders[$i]['pname']) .
                                    ' from ' . ucfirst($pastOrders[$i]['fcname']) . ')\');
                                document.getElementById(\'givefb\').setAttribute(\'type\',\'text\');
                                document.getElementById(\'viewfb\').setAttribute(\'type\',\'hidden\');
                                document.getElementById(\'divsfb\').setAttribute(\'style\',\'visibility: visible\');
                                document.getElementById(\'orderIdFb\').setAttribute(\'value\',\'' . $oid . '\');
                                document.getElementById(\'productIdFb\').setAttribute(\'value\',\'' . $pastOrders[$i]['pid'] . '\');
                                document.getElementById(\'businessIdFb\').setAttribute(\'value\',\'' . $pastOrders[$i]['bid'] . '\');
                                document.getElementById(\'r1\').setAttribute(\'style\',\'visibility: visible\');
                                document.getElementById(\'r2\').setAttribute(\'style\',\'visibility: visible\');
                                document.getElementById(\'r3\').setAttribute(\'style\',\'visibility: visible\');
                                document.getElementById(\'r4\').setAttribute(\'style\',\'visibility: visible\');
                                document.getElementById(\'r5\').setAttribute(\'style\',\'visibility: visible\');
                                document.getElementById(\'error_msg\').setAttribute(\'style\',\'visibility: hidden\');">
                                Give Feedback</button>';
                            } else {
                                $rows = getRows('orderId', $oid, 'orderfeedback');
                                $feedback = $rows['feedback'];
                                unset($rows);
                                echo '<button
                                onclick="
                                document.getElementById(\'viewfb\').setAttribute(\'value\',
                                \'(for Order no. ' . strval(intval($i) + 1) . ') ' . $feedback . ' \');
                                document.getElementById(\'viewfb\').setAttribute(\'type\',\'text\');      
                                document.getElementById(\'givefb\').setAttribute(\'type\',\'hidden\');                          
                                document.getElementById(\'divsfb\').setAttribute(\'style\',\'visibility: hidden\');
                                document.getElementById(\'r1\').setAttribute(\'style\',\'visibility: hidden\');
                                document.getElementById(\'r2\').setAttribute(\'style\',\'visibility: hidden\');
                                document.getElementById(\'r3\').setAttribute(\'style\',\'visibility: hidden\');
                                document.getElementById(\'r4\').setAttribute(\'style\',\'visibility: hidden\');
                                document.getElementById(\'r5\').setAttribute(\'style\',\'visibility: hidden\');
                                document.getElementById(\'error_msg\').setAttribute(\'style\',\'visibility: hidden\');">
                                View Feedback</button>';
                            }
                        }
                        echo '</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </table>
        </div>
        <br>
        <form method="post">
            <script>
                function r1() {
                    document.getElementById('r1').setAttribute('class', "fa fa-star");
                    document.getElementById('r2').setAttribute('class', "fa fa-star-o");
                    document.getElementById('r3').setAttribute('class', "fa fa-star-o");
                    document.getElementById('r4').setAttribute('class', "fa fa-star-o");
                    document.getElementById('r5').setAttribute('class', "fa fa-star-o");
                    document.getElementById('ratingFb').setAttribute('value', "1");
                }

                function r2() {
                    document.getElementById('r1').setAttribute('class', "fa fa-star");
                    document.getElementById('r2').setAttribute('class', "fa fa-star");
                    document.getElementById('r3').setAttribute('class', "fa fa-star-o");
                    document.getElementById('r4').setAttribute('class', "fa fa-star-o");
                    document.getElementById('r5').setAttribute('class', "fa fa-star-o");
                    document.getElementById('ratingFb').setAttribute('value', "2");
                }

                function r3() {
                    document.getElementById('r1').setAttribute('class', "fa fa-star");
                    document.getElementById('r2').setAttribute('class', "fa fa-star");
                    document.getElementById('r3').setAttribute('class', "fa fa-star");
                    document.getElementById('r4').setAttribute('class', "fa fa-star-o");
                    document.getElementById('r5').setAttribute('class', "fa fa-star-o");
                    document.getElementById('ratingFb').setAttribute('value', "3");
                }

                function r4() {
                    document.getElementById('r1').setAttribute('class', "fa fa-star");
                    document.getElementById('r2').setAttribute('class', "fa fa-star");
                    document.getElementById('r3').setAttribute('class', "fa fa-star");
                    document.getElementById('r4').setAttribute('class', "fa fa-star");
                    document.getElementById('r5').setAttribute('class', "fa fa-star-o");
                    document.getElementById('ratingFb').setAttribute('value', "4");
                }

                function r5() {
                    document.getElementById('r1').setAttribute('class', "fa fa-star");
                    document.getElementById('r2').setAttribute('class', "fa fa-star");
                    document.getElementById('r3').setAttribute('class', "fa fa-star");
                    document.getElementById('r4').setAttribute('class', "fa fa-star");
                    document.getElementById('r5').setAttribute('class', "fa fa-star");
                    document.getElementById('ratingFb').setAttribute('value', "5");
                }
            </script>
            <input type="hidden" value="" class="form-control  form-control-lg"
                   name="givefb" id="givefb" placeholder="">
            <span id="r1" onclick="r1()" style="visibility: hidden" class="fa fa-star-o"></span>
            <span id="r2" onclick="r2()" style="visibility: hidden" class="fa fa-star-o"></span>
            <span id="r3" onclick="r3()" style="visibility: hidden" class="fa fa-star-o"></span>
            <span id="r4" onclick="r4()" style="visibility: hidden" class="fa fa-star-o"></span>
            <span id="r5" onclick="r5()" style="visibility: hidden" class="fa fa-star-o"></span>
            <input type="hidden" value="" name="orderIdFb" id="orderIdFb">
            <input type="hidden" value="" name="productIdFb" id="productIdFb">
            <input type="hidden" value="" name="ratingFb" id="ratingFb">
            <input type="hidden" value="" name="businessIdFb" id="businessIdFb">
            <input type="hidden" value="" class="form-control  form-control-lg"
                   name="viewfb" id="viewfb" placeholder="" disabled>
            <div id="divsfb" style="visibility: hidden">
                <button type="submit" name="submitfb" class="btn btn-primary ">Submit Feedback</button>
            </div>

        </form>
        <?php

        ?>

    </section>

<footer class="footer">
    <div class="container-fluid">
        <div class="row text-muted">
            <div class="col-6 text-left">
                <p class="mb-0">
                    <a href="userpage.php" class="text-muted"><strong>FooDino </strong></a> &copy
                </p>
            </div>
            <div class="col-6 text-right">
                <ul class="list-inline">
                    <li class="footer-item">
                        <a class="text-muted" href="#">Support</a>
                    </li>
                    <li class="footer-item">
                        <a class="text-muted" href="#">Help Center</a>
                    </li>
                    <li class="footer-item">
                        <a class="text-muted" href="#">Privacy</a>
                    </li>
                    <li class="footer-item">
                        <a class="text-muted" href="#">Terms</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>


</div>


<script src="bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"
        integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s"
        crossorigin="anonymous"></script>
</body>
</html>