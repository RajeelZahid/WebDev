<script>document.getElementById("qty").value="1"</script>

<?php
if (!isset($_SESSION)) {
    session_start();
}
$_SESSION['CURRENTLY_IN'] = $_SERVER['PHP_SELF'];
require('cust.top.inc.php');
$user = $_SESSION['USERNAME'];

$msg = '';
$error = 0;

if (array_key_exists('prod', $_GET)){
    if ($_GET['prod'] != '') {
        $productIdGet = $_GET['prod'];
        if (isContain('productId', $productIdGet, 'productdetail') == 0) {
            $error=1; $msg='There is No Product for This ID';
        }
    } else {
        $error=1; $msg='Product Not Selected';
    }
} else {
    $error=1; $msg='Product Not Selected';
}

if ($error == 0) { ?>
<?php
if (array_key_exists('prod', $_GET) &&
    (!array_key_exists('addtostove', $_GET) || !array_key_exists('buyitnow', $_GET) )) {
    if ($_GET['prod'] != '') {
        $productIdGet = $_GET['prod'];
        if (isContain('productId', $productIdGet, 'productdetail') == 1) {
            $PRODUCT['detailsprod-PRODUCTID'] = $productIdGet;
        }
        $productSelected = 1;
    } else {
        $productSelected = 0;
    }
} else {
    $productSelected = 0;
}

if (array_key_exists('detailsprod-PRODUCTID', $PRODUCT)) {
    $univ_Qty='1';
    $pid = $PRODUCT['detailsprod-PRODUCTID'];
    $feedbackDetail = [];
    $isExistInStove = isExistWRTothField('productId', $pid, 'customerId', $user, 'stove');
    if ($isExistInStove == 1) {
        $sql = "select * from stove where customerId='$user' and productId='$pid'";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_array($res);
        $univ_Qty = $row['quantity'];
        $univ_Tp = $row['totalPrice'];
    } else {
        $row = getRows('productId', $pid, 'productdetail');
        $univ_Qty="1";
        $univ_Tp = $row['productPrice'];
    }
    $hasFeedbacks = isContain('productId', $pid, 'orderfeedback');
    if ($hasFeedbacks == 1) $feedbackDetail = getRowsOfSameEntry('productId', $pid, 'orderfeedback');
    else $feedback_msg = 'No Feedbacks for this product yet';
}

if (array_key_exists('prod', $_GET) && array_key_exists('qty', $_GET) &&
    array_key_exists('addtostove', $_GET) && !array_key_exists('buyitnow', $_GET) ) {
    $productIdGet = get_safe_values($con, $_GET['prod']);
    $qty = get_safe_values($con, $_GET['qty']);
    $productDetail=getRows('productId', $productIdGet, 'productdetail');
    $totalPrice = intval($qty)*intval($productDetail['productPrice']);
    $products = getColumns('stove', 'productId', 'customerId', $user);
    $countProds = count($products);
    $addedDate = date('Y-m-d');
    if ($countProds == 0 || in_array($productIdGet, $products) == 0) {
        $sql = "INSERT INTO
            stove(customerId, productId, quantity, totalPrice, addedDate)
            VALUES ('$user','$productIdGet','$qty','$totalPrice','$addedDate')";

    } else {
        $sql = "UPDATE stove
                    SET quantity = '$qty', totalPrice='$totalPrice', addedDate='$addedDate'
					WHERE customerId='$user' and productId='$productIdGet'";
    }
    mysqli_query($con, $sql);

    echo '<script>document.getElementById("qty").value="1"</script>';

    header('location:stovepage.php');
}

if (array_key_exists('prod', $_GET) && array_key_exists('qty', $_GET) &&
    !array_key_exists('addtostove', $_GET) && array_key_exists('buyitnow', $_GET) ) {
    $_SESSION['stoveItemDetails'] = [];
    $_SESSION['stoveItems'] = [];

    $productIdGet = get_safe_values($con, $_GET['prod']);
    $qty = get_safe_values($con, $_GET['qty']);
    $productDetail=getRows('productId', $productIdGet, 'productdetail');
    $totalPrice = intval($qty)*intval($productDetail['productPrice']);

    $addedDate = date('Y-m-d');
    $itemRow = getRows('productId', $productIdGet, 'productdetail');

    $_SESSION['stoveItemDetails'][0] = $itemRow;
    $_SESSION['stoveItems'][0]['customerId'] = $user;
    $_SESSION['stoveItems'][0]['productId'] = $productIdGet;
    $_SESSION['stoveItems'][0]['quantity'] = $qty;
    $_SESSION['stoveItems'][0]['totalPrice'] = $totalPrice;
    $_SESSION['stoveItems'][0]['addedDate'] = $addedDate;
    $totalPrice = intval($totalPrice);
    $_SESSION['totalAmount'] = $totalPrice + ($totalPrice * 1.5 / 100);

    echo '';

    header('location:checkout.php');
}

if (array_key_exists('goToStove', $_POST)) {
    header('location:stovepage.php');
}
?>
<?php } ?>

<!doctype html>
<html lang="en">

<head>
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
-->
    <link rel="stylesheet" href="../site/style.css">
    <link rel="stylesheet" href="../site/css2.css">
    <link rel="stylesheet" href="../site/css/font-awesome.min.css">
    <link rel="stylesheet" href="../site/bootstrap.min.css">

    <link rel="stylesheet" href="../site/bootstrap.min.css">
    <link rel="stylesheet" href="../site/style.css">
    <link rel="stylesheet" href="../site/css/font-awesome.min.css">
    <link rel="stylesheet" href="../site/animate.css">
    <link rel="stylesheet" href="../site/owl.carousel.css">
    <link rel="stylesheet" href="../site/owl.theme.default.min.css">
    <link rel="stylesheet" href="../site/magnific-popup.css">
    <title>Product Detail | FooDino</title>

    <!-- js -->
    <script src="../files/includehtml.js"></script>

</head>

<body>
<div include-html="customer_header.php"></div>

<script>
    includeHTML();
</script>
<?php if ($error==0){ ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div id="content" class="content content-full-width">
                    <!-- begin profile -->
                    <div class="profile">

                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">

                <div class="col-md-8 col-lg-9">
                    <div class="">
                        <div class="">
                            <ul class="nav nav-tabs navtab-custom">
                                <script>
                                    function fbacks() {
                                        document.getElementById('fb').setAttribute('class', 'active');
                                        document.getElementById('pr').setAttribute('class', 'notactive');
                                    }

                                    function prods() {
                                        document.getElementById('pr').setAttribute('class', 'active');
                                        document.getElementById('fb').setAttribute('class', 'notactive');
                                    }
                                </script>

                                <li class="active" id="pr">
                                    <a href="#detail" onclick="prods()" data-toggle="tab" aria-expanded="true">
                                        <span class="visible-xs"><i class="fa fa-book"></i></span>
                                        <span class="hidden-xs">Detail</span>
                                    </a>
                                </li>

                                <li class="notactive" id="fb">
                                    <a href="#feedbacks" onclick="fbacks()" data-toggle="tab" aria-expanded="false">
                                        <span class="visible-xs"><i class="fa fa-star"></i></span>
                                        <span class="hidden-xs">Feedbacks</span>
                                    </a>
                                </li>
                            </ul>
                            <hr>
                            <div class="tab-content">
                                <?php
                                if ($productSelected == 1) {
                                    $productCount = getCount('productId', $productIdGet, 'productdetail');
                                    if ($productCount > 0) {
                                        $row = getRows('productId', $productIdGet, 'productdetail');
                                        $imageFile = $row['folderName'] . '/' . $row['fileName'];
                                        $rating = 0;
                                        if ($hasFeedbacks == 1) {
                                            $sum_rating = 0;
                                            for ($i = 0; $i < count($feedbackDetail); $i++) {
                                                $sum_rating = $sum_rating + intval($feedbackDetail[$i]['rating']);
                                            }
                                            $rating = $sum_rating / count($feedbackDetail);
                                        }
                                        echo '
                                <div class="tab-pane active" id="detail">
                                    <section class="mb-5">
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-5 col-lg-3 col-xl-6">
                                                <!-- <div class="view zoom overlay z-depth-1 rounded mb-3 mb-md-0">-->
                                                <img class="img-fluid max-width w-100"
                                                     src="../db/' . $imageFile . '" alt="Sample">
                                                <a href="#!">
                                                </a>

                                            </div>
                                            <div class="col-md-6">

                                                <h5>' . ucfirst($row["productName"]) . '</h5> by
                                                <a href="vendordetail.php?vend=' . $row["businessVendorId"] . '"
                                                onclick="vendorInfoPage()"
                                                class="food-card_author"><br> ' . ucfirst($row["businessName"]) . '</a>
                                                <div class="rating">
                                                    <div class="stars">';
                                        for ($j = 0; $j < intval($rating); $j++) {
                                            echo '<span class="fa fa-star checked"
                                                                    style="color: rgb(255, 249, 63);"></span>';
                                        }
                                        for ($k = 5; $k > $j; $k--) {
                                            echo '<span class="fa fa-star-o"></span>';
                                        }
                                        echo '<span class="review-no"> (' . $rating . ')</span>
                                                    </div>
                                                    <span class="review-no">' . count($feedbackDetail) . ' reviews</span>
                                                </div>
                                                <p class="mb-2 text-muted text-uppercase small"></p>
                                                <ul class="rating">

                                                </ul>
                                                <p><span class="mr-1"><strong>' . $row["productWeight"] . ' KG @
            ' . $row["productPrice"] . ' Rs</strong></span></p>
                                                <p class="pt-1">' . $row["prodDescrip"] . '</p>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tbody>
                                                        <tr>
                                                            <th class="pl-0 w-25" scope="row">Category</th>
                                                            <td style="color:#54229d"><strong>' .
                                            $row["productCategorie"] . '</strong></td>
                                                        </tr>

                                                        <tr>
                                                            <th class="pl-0 w-25" scope="row">Delivery</th>
                                                            <td style="color:#54229d"><strong>Karachi</strong></td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <hr>
                                                <div class="table-responsive mb-2">
                                                    <table class="table table-sm table-borderless">

                                                    </table>
                                                </div>
                                                    <input type="hidden" value="' . $row["productPrice"] . '"
                                                    id="perPrice"/>
                                                    <input type="hidden" value="' . $productIdGet. '"
                                                    id="prod"/>
                                                    <input onchange="totalPrice()" id="qty" name="qty" class="quantity"
                                                           min="1" max="200"
                                                           value="' . $univ_Qty . '" type="number">
                                                    <p class="mb-0">Total Price: <span
                                                                id="totalPrice"></span>
                                                    </p class="mb-0" >
                                                    <script> 
                                                        function totalPrice() {
                                                            num1 = document.getElementById("qty").value;
                                                            num2 = document.getElementById("perPrice").value;
                                                            document.getElementById("totalPrice").innerHTML = num1 * num2;
                                                            document.getElementById("totalPriceTb").value = num1 * num2;
                                                        }
                                                        num1 = document.getElementById("qty").value;
                                                        num2 = document.getElementById("perPrice").value;
                                                        document.getElementById("totalPrice").innerHTML = num1 * num2;
                                                        document.getElementById("totalPriceTb").value = num1 * num2;
                                                     </script>
                                                   
                                                    <a href="" 
                                                    onclick="this.href=\'detailsprod.php?\'+
                                                    \'prod=\'+document.getElementById(\'prod\').value+
                                                    \'&qty=\'+document.getElementById(\'qty\').value+
                                                    \'&addtostove=\'">
                                                        <button class="btn btn-primary">Add to Stove</button>
                                                    </a>
                                                    
                                                    <a href="" 
                                                    onclick="this.href=\'detailsprod.php?\'+
                                                    \'prod=\'+document.getElementById(\'prod\').value+
                                                    \'&qty=\'+document.getElementById(\'qty\').value+
                                                    \'&buyitnow=\'">
                                                        <button class="btn btn-primary">Buy It Now</button>
                                                    </a>
                                            </div>
                                        </div>
                                </section>
                            </div>
                            <div class="tab-pane" id="feedbacks">
                                <div class="m-t-30">';
                                        if ($hasFeedbacks == 1) {
                                            shuffle($feedbackDetail);
                                            for ($i = 0; $i < count($feedbackDetail); $i++) {
                                                if ($feedbackDetail[$i]['visible'] == 'yes') {
                                                    $productId = getEntry('orderdetail', 'productId',
                                                        'orderId', $feedbackDetail[$i]['orderId']);
                                                    $productName = getEntry('productdetail', 'productName',
                                                        'productId', $productId);
                                                    echo '
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    
                                                <div class="col-md-2">
                                                    <p class="text-secondary text-center">' . $feedbackDetail[$i]['time'] . '</p>
                                                </div>
                                                <div class="col-md-10">
                                                    <p>
                                                        <a class="float-left">
                                                        <strong>' . $feedbackDetail[$i]['customerId'] . '@</strong>
                                                        </a>
                                                        <a href="detailsprod.php?prod=' . $productId . '">
                                                            ' . ucwords($productName) . '
                                                        </a>';
                                                    for ($st = 5; $st > intval($feedbackDetail[$i]['rating']); $st--) {
                                                        echo '<span class="float-right"><span class="fa fa-star-o "></span></span>';
                                                    }
                                                    for ($st = 0; $st < intval($feedbackDetail[$i]['rating']); $st++) {
                                                        echo '<span class="float-right"><span class="fa fa-star checked" style="color: rgb(255, 249, 63);"></span></span>';
                                                    }
                                                    echo '
                                                    </p>
                                                    <div class="clearfix"></div>
                                                    <p>' . $feedbackDetail[$i]['feedback'] . '
                                                    </p>
                                                </div>
                                            </div>
                                        </div>';
                                                }
                                            }
                                        } else {
                                            echo '<h6 align="center"> ' . $feedback_msg . '</h6>';
                                        }
                                    } else {
                                        echo '
                                <div class="col-md-7 col-lg-7 col-sm-12 col-xs-12 smt-40 xmt-40">
                                            <div class="ht__product__dtl">
                                                <h3>No product found for this product ID</h3>';
                                        die();
                                    }
                                } else {
                                    echo '
                                                <div class="col-md-7 col-lg-7 col-sm-12 col-xs-12 smt-40 xmt-40">
                                                    <div class="ht__product__dtl">
                                                        <h3>No product Selected</h3>';
                                    die();
                                }
                                ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php }
else echo '<div class="py-5 text-center">

        <h2>'.$msg.'</h2>
        <a href="userfoodino.php?city=karachi" color="cyan">Select Product From Here</a>
    </div>';
?>
<hr>
<div>
    <footer class="footer">
        <div class="container-fluid ">
            <div class="row text-muted">
                <div class="col-6 text-left">
                    <p class="mb-0">
                        <a href="index.html" class="text-muted">FooDino</a> &copy
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
<script src="../site/jquery.js"></script>
<script src="../site/jquery.stellar.min.js"></script>
<script src="../site/wow.min.js"></script>
<script src="../site/owl.carousel.min.js"></script>
<script src="../site/jquery.magnific-popup.min.js"></script>
<script src="../site/smoothscroll.js"></script>
<script src="../site/custom.js"></script>

</body>