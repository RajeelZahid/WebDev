<?php
require('seller.top.inc.php');
$SELLER_ID = $_SESSION['USERID'];

$count = getCount('businessId', $SELLER_ID, 'orders');
if ($count > 0) {
    $orderIDs2 = getColumns('orders', 'orderId', 'businessId', $SELLER_ID);
    $orderIDs = array_unique($orderIDs2);
    $orderDetail = [];
    for ($i = 0; $i < count($orderIDs); $i++) {
        $orderID = $orderIDs[$i];
        $sql = "select * from orderdetail where businessId= '$SELLER_ID'  and orderId='$orderID'";
        $res = mysqli_query($con, $sql);
        $orderDetail[$i] = mysqli_fetch_array($res);
        for ($j = 0; $j < count($orderDetail[$i]); $j++) {
            unset($orderDetail[$i][$j]);
        }
    }

    $ac = 0;
    $pa = 0;
    $activeOrders = [];
    $pastOrders = [];
    date_default_timezone_set('Asia/Karachi');
    $timeNow = date("Y/m/d") . ' ' . date("H:i:s");
    for ($i = 0; $i < count($orderDetail); $i++) {
        $allOrders[$i]['id'] = $orderDetail[$i]["orderId"];
        $products = getAllRows_2fields('orderdetail', 'businessId', $SELLER_ID, 'orderId', $allOrders[$i]['id']);
        $allOrders[$i]['cname'] = $orderDetail[$i]["customerName"];
        $allOrders[$i]['contact'] = $orderDetail[$i]["customerPhone"];
        $allOrders[$i]['email'] = $orderDetail[$i]["customerEmail"];
        $allOrders[$i]['addtime'] = $orderDetail[$i]["orderAddedTime"];
        $allOrders[$i]['deltime'] = $orderDetail[$i]["deliveryTime"];
        $allOrders[$i]['status'] = $orderDetail[$i]["orderStatus"];
        $allOrders[$i]['statusid'] = $orderDetail[$i]["orderStatusId"];
        $allOrders[$i]['updtime'] = $orderDetail[$i]["orderStatusUpdatedTime"];
        if ($allOrders[$i]['statusid'] == "w") {
            $allOrders[$i]['status'] = "Waiting for your response";
        }
        $active=0;
        for ($k = 0; $k < count($products); $k++) {
            if($products[$k]['orderStatusId']=='a' && $timeNow>$allOrders[$i]['deltime']){
                $active=0;
                break;
            }
            elseif($products[$k]['orderStatusId']=='w' ||$products[$k]['orderStatusId']=='a'){
                $active=1;
                break;
            }
        }

        if ($active==1) {
            $activeOrders[$ac] = $allOrders[$i];
            $ac++;
        }
        if ($active==0) {
            $pastOrders[$pa] = $allOrders[$i];
            $pa++;
        }
    }
}

unset($allOrders, $ac, $pa);

if (array_key_exists('a_sortby', $_GET)) {
    $sortby = $_GET['a_sortby'];
    if ($sortby == 'cname' || $sortby == 'addtime' || $sortby == 'deltime') {
        $activeOrders = change_value2key($activeOrders, $sortby);
        if (!array_key_exists('sort', $_SESSION) || $_SESSION['sort'] == 'asc') {
            ksort($activeOrders);
            $_SESSION['sort'] = 'desc';
        } else if ($_SESSION['sort'] == 'desc') {
            krsort($activeOrders);
            $_SESSION['sort'] = 'asc';
        }
        $activeOrders = array_values($activeOrders);
    }
}

if (array_key_exists('p_sortby', $_GET)) {
    $sortby = $_GET['p_sortby'];
    if ($sortby == 'cname' || $sortby == 'addtime' || $sortby == 'deltime') {
        $pastOrders = change_value2key($pastOrders, $sortby);
        if (!array_key_exists('sort', $_SESSION) || $_SESSION['sort'] == 'asc') {
            ksort($pastOrders);
            $_SESSION['sort'] = 'desc';
        } else if ($_SESSION['sort'] == 'desc') {
            krsort($pastOrders);
            $_SESSION['sort'] = 'asc';
        }
        $pastOrders = array_values($pastOrders);
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../site/bootstrap.min.css">
    <link rel="stylesheet" href="../site/styy.css">
    <link rel="stylesheet" href="../site/css/font-awesome.min.css">


    <title>View Orders | FooDino</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&display=swap"
          rel="stylesheet">
    <!--fontawesome-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">


</head>


<body>

<?php
if ($count == 0) {
    echo '<div class="container">
    <div class="py-5 text-center">

        <h2>You do not have any orders yet</h2>
    </div>';
    die();
}
?>

<div id="wrapper">
    <div class="overlay"></div>

    <!-- Sidebar -->
    <nav class="fixed-top align-top " id="sidebar-wrapper" role="navigation">
        <div class="simplebar-content " style="padding: 0px;">
            <a class="sidebar-brand" href="index.html">
                <span class="align-middle">FooDino</span>
            </a>

            <ul class="navbar-nav align-self-stretch">
                <li class="">
                    <a class="nav-link text-left active" role="button"
                       aria-haspopup="true" aria-expanded="false">
                        <i class="flaticon-bar-chart-1"></i> Dashboard
                    </a>
                </li>

                <li class="sidebar-header">

                </li>

                <li>
                    <a href="sellerdashboard.php"><i class="fa fa-home" aria-hidden="true"></i>Home</a>
                </li>
                <li>
                    <a href="myinfo.php"><i class="fa fa-info" aria-hidden="true"></i> Myinfo</a>
                </li>
                <li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"> <i
                                class="fas fa-tasks"></i> Product Management</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li>
                            <a style="color:rgba(216, 211, 211, 0.658);" href="addproduct.php"><i
                                        class="fa fa-user-plus" aria-hidden="true"></i> Add</a>
                        </li>
                        <li>
                            <a style="color:rgba(216, 211, 211, 0.658);" href="modifyproduct.php"><i
                                        class="fas fa-user-edit"></i> Modify</a>
                        </li>

                    </ul>
                </li>
                <li>
                    <a href="orderview.php"><i class="fab fa-first-order"></i>Order</a></li>
                <li>
                    <a href=""> <i class="fa fa-credit-card" aria-hidden="true"></i>Payment</a>
                </li>
                <li>
                    <a href=""><i class="fa fa-cog" aria-hidden="true"></i> Setting</a>
                </li>


        </div>
        <div class="nav-link ">
            <a type="button" class="btn btn-light btn-block" onclick=" window.location = 'logout.php'">
                Logout
            </a>
        </div>

    </nav>
    <!-- /#sidebar-wrapper -->


    <!-- Page Content -->
    <div id="page-content-wrapper">


        <div id="content">

            <div class="container-fluid p-0 px-lg-0 px-md-0">

                <section class="container">
                    <legend>ACTIVE ORDER</legend>
                    <div class="table-responsive">
                        <table class="table">
                            <table id="activeOrder" class="table">
                                <thead>
                                <tr style="background-color:#eaeaea">
                                    <th scope="col">
                                        <a href="?a_sortby=id">
                                            ID
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="?a_sortby=cname">
                                            Customer Name
                                        </a>
                                    </th>
                                    <th scope="col"><a href="#">Contact</a></th>
                                    <th scope="col"><a href="#">Email</a></th>
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
                                        <a href="">
                                            Items
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="">
                                            Status
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="">
                                            Updated Time
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < count($activeOrders); $i++) {
                                    $products = getAllRows_2fields('orderdetail', 'businessId', $SELLER_ID, 'orderId', $activeOrders[$i]['id']);
                                    echo
                                        '<tr>
                        <th scope="col">
                            <a href="orderdetail.php?id=' . $activeOrders[$i]['id'] . '">
                                ' . $i . '
                            </a>
                        </th>
                        <td>' . ucfirst($activeOrders[$i]['cname']) . '</td>
                        <td>' . $activeOrders[$i]['contact'] . '</td>
                        <td>' . $activeOrders[$i]['email'] . '</td>
                        <td>' . $activeOrders[$i]['addtime'] . '</td>
                        <td>' . $activeOrders[$i]['deltime'] . '</td>
                        <td>';
                                    for ($k = 0; $k < count($products); $k++) {
                                        $pname = getEntry('productdetail', 'productName', 'productId', $products[$k]['productId']);
                                        echo ucwords($pname) . '<br>';
                                    }
                                    echo '
                        </td>
                        <td>';
                                    for ($k = 0; $k < count($products); $k++) {
                                        if ($products[$k]['orderStatusId'] == "w") {
                                            $products[$k]['orderStatus'] = "Waiting for your response";
                                        }
                                        echo $products[$k]['orderStatus'] . '<br>';
                                    }
                                    echo '
                        </td>
                        <td>';
                                    for ($k = 0; $k < count($products); $k++) {
                                        echo $products[$k]['orderStatusUpdatedTime'] . '<br>';
                                    }
                                    echo '
                        </td>
                    </tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </table>
                    </div>
                </section>
                <p color="purple" align="center">Click ID number of an order to get full detail</p>
                <hr>
                <br><br>

                <section class="container">
                    <legend>PAST ORDER</legend>
                    <div class="table-responsive">
                        <table class="table">
                            <table id="pastOrder" class="table">
                                <thead>
                                <tr style="background-color:#eaeaea">
                                    <th scope="col">
                                        <a href="?p_sort=id">
                                            ID
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="?p_sort=cname">
                                            Customer Name
                                        </a>
                                    </th>
                                    <th scope="col"><a href="#">Contact</a></th>
                                    <th scope="col"><a href="#">Email</a></th>
                                    <th scope="col">
                                        <a href="?p_sort=addtime">
                                            Added Time
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="?p_sortby=deltime">
                                            Delivery Time
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="">
                                            Items
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="">
                                            Status
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="">
                                            Updated Time
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < count($pastOrders); $i++) {
                                    $products = getAllRows_2fields('orderdetail', 'businessId', $SELLER_ID, 'orderId', $pastOrders[$i]['id']);
                                    echo
                                        '<tr>
                        <th scope="col">
                            <a href="orderdetail.php?id=' . $pastOrders[$i]['id'] . '">
                                ' . $i . '
                            </a>
                        </th>
                        <td>' . ucfirst($pastOrders[$i]['cname']) . '</td>
                        <td>' . $pastOrders[$i]['contact'] . '</td>
                        <td>' . $pastOrders[$i]['email'] . '</td>
                        <td>' . $pastOrders[$i]['addtime'] . '</td>
                        <td>' . $pastOrders[$i]['deltime'] . '</td>
                        <td>';
                                    for ($k = 0; $k < count($products); $k++) {
                                        $pname = getEntry('productdetail', 'productName', 'productId', $products[$k]['productId']);
                                        echo ucwords($pname) . '<br>';
                                    }
                                    echo '
                        </td>
                        <td>';
                                    for ($k = 0; $k < count($products); $k++) {
                                        if ($products[$k]['orderStatus'] == "Active") {
                                            $products[$k]['orderStatus'] = "Time up";
                                        }
                                        echo $products[$k]['orderStatus'] . '<br>';
                                    }
                                    echo '
                        </td>
                        <td>';
                                    for ($k = 0; $k < count($products); $k++) {
                                        echo $products[$k]['orderStatusUpdatedTime'] . '<br>';
                                    }
                                    echo '
                        </td>
                    </tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </table>
                    </div>
                </section>
                <p color="purple" align="center">Click ID number of an order to get full detail</p>
                <hr>
            </div>
<br>

            <!--            <footer class="footer" style="bottom: 0; position: sticky;">-->
            <footer class="footer" style="position: ; left: 0;
    bottom: 0;
    width: 100%;
    text-align: center;">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-left">
                            <p class="mb-0">
                                <a href="index.html" class="text-muted"><strong>FooDino </strong></a> &copy
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
    </div>
    <!-- /#page-content-wrapper -->

</div>
<!-- /#wrapper -->


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
        crossorigin="anonymous"></script>


<script>

    $('#bar').click(function () {
        $(this).toggleClass('open');
        $('#page-content-wrapper ,#sidebar-wrapper').toggleClass('toggled');

    });
</script>


</body>
</html>
