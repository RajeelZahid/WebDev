<?php
if (!isset($_SESSION)) {
    session_start();
}
$_SESSION['CURRENTLY_IN'] = $_SERVER['PHP_SELF'];
require('cust.top.inc.php');
$customerId = $_SESSION['USERNAME'];

if (array_key_exists('removeItem', $_GET) && array_key_exists('pid', $_GET)) {
    $pid = $_GET['pid'];
    $id = $_GET['removeItem'];
    if ($_SESSION['stoveItems'][$id]['productId'] == $pid) {
        $tp = $_SESSION['totalAmount'];
        $tp = strval((100 * $tp) / (100 + 1.5));
        $prp = $_SESSION['stoveItems'][$id]['totalPrice'];
        $tp = $tp - $prp;
        $tp = $tp + ($tp * 1.5 / 100);
        $_SESSION['totalAmount'] = $tp;
        array_splice($_SESSION['stoveItemDetails'], $id, 1);
        array_splice($_SESSION['stoveItems'], $id, 1);
        $sql = "delete from stove where productId='$pid' and customerId='$customerId'";
        mysqli_query($con, $sql);
        header('location:stovepage.php');
    } else {
        header('location:stovepage.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Stove | FooDino</title>
    <link rel="stylesheet" href="../site/bootstrap.min.css">
    <link rel="stylesheet" href="../site/style.css">
    <link rel="stylesheet" href="../site/css/font-awesome.min.css">
    <link rel="stylesheet" href="../site/animate.css">
    <link rel="stylesheet" href="../site/owl.carousel.css">
    <link rel="stylesheet" href="../site/owl.theme.default.min.css">
    <link rel="stylesheet" href="../site/magnific-popup.css">


    <!-- MAIN CSS -->

    <link rel="stylesheet" href="../site/style.css">
    <link rel="stylesheet" href="../site/demo.css">
    <link rel="stylesheet" href="../site/intlTelInput.css">

    <script src="../files/includehtml.js"></script>

</head>

<body>
<div include-html="customer_header.php"></div>


<script>
    includeHTML();
</script>


<!--Section: Block Content-->
<section>
    <div class="container">
        <!--Grid row-->
        <div class="row">

            <!--Grid column-->
            <div class="col-lg-8">

                <!-- Card -->
                <div class="mb-3">
                    <div class="pt-4 wish-list">
                        <?php
                        $totStoveItems = getCount('customerId', $customerId, 'stove');
                        ?>

                        <h5 class="mb-4">On Stove (<span><?php echo $totStoveItems ?></span>
                            <?php if ($totStoveItems > 1) echo 'items';
                            else echo 'item'; ?>)
                        </h5>

                        <?php
                        if ($totStoveItems > 0) {
                            $stoveItems = getDataWRTOtherTable('productdetail', 'productId', 'productId',
                                'stove', 'productId', 'customerId', $customerId);
                            $_SESSION['stoveItemDetails'] = $stoveItems;
                            $itemRow = getRowsOfSameEntry('customerId', $customerId, 'stove');
                            $_SESSION['stoveItems'] = $itemRow;
                            for ($i = 0; $i < $totStoveItems; $i++) {
                                echo '
                        <div class="row mb-4" >
                            <div class="col-md-5 col-lg-3 col-xl-3" >
                                <div class="" >
                                    
                                    <a href = "" >
                                        <div class="mask" >
                                            <img class="img-fluid w-100"
                                                 src = "../db/' . $stoveItems[$i]["folderName"] . '/' . $stoveItems[$i]["fileName"] . '" >
                                            <div class="mask rgba-black-slight" ></div >
                                        </div >
                                    </a >
                                </div >
                            </div>
                            <div class="col-md-7 col-lg-9 col-xl-9" >
                                <div >
                                    <div class="d-flex justify-content-between" >
                                        <div >
                                        <a href="detailsprod.php?prod=' . $stoveItems[$i]["productId"] . '">'
                                    . ucfirst($stoveItems[$i]["productName"]) . '</a >
                                           <a href="vendordetail.php?vend=' . $stoveItems[$i]["businessVendorId"] . '">
                                            <p class="mb-3 text-muted text-uppercase small" > by <br>' . $stoveItems[$i]["businessName"] . '
                                            </p ></a>
                                            <p class="mb-2 text-muted text-uppercase small" > Category: ' . $stoveItems[$i]["productCategorie"] . '</p >
                                            <p class="mb-3 text-muted text-uppercase small" > weight: ' . $stoveItems[$i]["productWeight"] . ' Kg</p >
                                            <p class="mb-3 text-muted text-uppercase small" id="perPrice"> Per Price: ' . $stoveItems[$i]["productPrice"] . ' Rs </p >
                                        </div >
                                        <div >
                                            <div class="def-number-input number-input safari_only mb-0 w-100" >
                                                <input class="quantity" value="' . $itemRow[$i]["quantity"] . '">
                                            </div >
                                            <small id = "passwordHelpBlock" class="form-text text-muted text-center" >
                            (Note, 1 piece)
                                            </small >
                                        </div >
                                    </div >
                                    <div class="d-flex justify-content-between align-items-center" >
                                        <div >
                                            <a color="f25959" href="stovepage.php?removeItem=' . array_keys($stoveItems)[$i] . '&pid=' . $stoveItems[$i]["productId"] . '" type = "button"
                                               class="card-link-secondary small text-uppercase mr-3" ><i
                                                        class="fas fa-trash-alt mr-1" ></i > Remove item </a >
                                            
                                        </div >
                                        <p class="mb-0" id="totalPrice">Total Price: <span ><strong id = "summary" > ' . $itemRow[$i]["totalPrice"] . ' Rs </strong ></span >
                                        </p class="mb-0" >
                                    </div >
                                </div >
                            </div >
                            
                        </div >
                        <hr class="mb-4" >';
                            }
                            echo '<div class="col-md-6 d-sm-inline-block">
                            <a href="userfoodino.php?city=karachi"><button class="btn btn-primary">Add more Items
                            </button></a>
                        </div>
                        <hr class="mb-4" >';
                        } else {
                            echo '<div class="col-md-6 d-sm-inline-block">
                            <a href="userfoodino.php?city=karachi"><button class="btn btn-primary">Add Items
                            </button></a>
                        </<div>';
                            echo '<!-- INSERT CODE <WHEN NO ITEMS IN STOVE> -->';
                        }
                        ?>

                        <p class="text-primary mb-0"><br><i class="fas fa-info-circle mr-1"></i> Do not delay the
                            purchase,
                            adding items to your stove does not mean booking them.</p>

                    </div>
                </div>
                <!-- Card -->


                <!-- Card -->

                <!-- Card -->
                <div class="mb-3">
                    <div class="pt-4">

                        <h5 class="mb-4">We accept</h5>

                        <img class="mr-2" width="45px"
                             src="https://mdbootstrap.com/wp-content/plugins/woocommerce-gateway-stripe/assets/images/visa.svg"
                             alt="Visa">
                        <img class="mr-2" width="45px"
                             src="https://mdbootstrap.com/wp-content/plugins/woocommerce-gateway-stripe/assets/images/amex.svg"
                             alt="American Express">
                        <img class="mr-2" width="45px"
                             src="https://mdbootstrap.com/wp-content/plugins/woocommerce-gateway-stripe/assets/images/mastercard.svg"
                             alt="Mastercard">
                        <img class="mr-2" width="45px"
                             src="https://1.bp.blogspot.com/-YOJRlSOwM-A/XGYjC-3_EOI/AAAAAAAAAfs/Z4JiMvKeEhgvwKyhfFxeXtSk8QCur7o6ACLcBGAs/s1600/20190215_072352.png"
                             alt="easypaisa">
                    </div>
                </div>
                <!-- Card -->

            </div>
            <!--Grid column-->

            <!--Grid column-->
            <?php if ($totStoveItems > 0) {
                echo '
            <div class="col-lg-4">
                <h4>Summary</h4>

                <!-- Card -->
                <div class="mb-3">
                    <div class="pt-4">
                        

                        <h5 class="mb-3">Bill</h5>';
                $total = 0;
                for ($i = 0; $i < $totStoveItems; $i++) {
                    echo '
<ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-0">
    ' . $itemRow[$i]["quantity"] . ' ' . $stoveItems[$i]['productName'] . '
    <span>' . $itemRow[$i]['totalPrice'] . '</span>
                            </li>';
                    $total = $total + $itemRow[$i]['totalPrice'];
                }
                $shipping = $total * 1.5 / 100;
                $total = $total + $shipping;
                echo '
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Shipping
                                <span>' . $shipping . '</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 mb-3">
                                <div>
                                    <strong>The total amount of</strong>
                                    <strong>
                                        <p class="mb-0">(including VAT)</p>
                                    </strong>
                                </div>
                                <span><strong>' . $total . '</strong></span>
                            </li>
                        </ul>

                        <a href="checkout.php"><button type="button" class="btn btn-primary btn-block">Light up the Match</button>
                        </a>
                    </div>
                </div>
                <!-- Card -->

                <!-- Card -->
                <div class="mb-3">
                    <div class="pt-4">

                        <a class="dark-grey-text d-flex justify-content-between" data-toggle="collapse"
                           href="#collapseExample"
                           aria-expanded="false" aria-controls="collapseExample">
                            Add a discount code (optional)
                            <span><i class="fas fa-chevron-down pt-1"></i></span>
                        </a>

                        <div class="collapse" id="collapseExample">
                            <div class="mt-3">
                                <div class="md-form md-outline mb-0">
                                    <input type="text" id="discount-code" class="form-control font-weight-light"
                                           placeholder="Enter discount code">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card -->

            </div>
            ';
                $_SESSION['totalAmount'] = $total;
            } ?>
            <!--Grid column-->

        </div>
        <!-- Grid row -->

</section>
<!--Section: Block Content-->
</div>


<hr>


<footer class="footer">
    <div class="container-fluid">
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


<script src="../site/intlTelInput.js"></script>
<script>
    var input = document.querySelector("#phone");
    window.intlTelInput(input, {
        // allowDropdown: false,
        // autoHideDialCode: false,
        // autoPlaceholder: "off",
        // dropdownContainer: document.body,
        // excludeCountries: ["us"],
        // formatOnDisplay: false,
        // geoIpLookup: function(callback) {
        //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
        //     var countryCode = (resp && resp.country) ? resp.country : "";
        //     callback(countryCode);
        //   });
        // },
        // hiddenInput: "full_number",
        // initialCountry: "auto",
        // localizedCountries: { 'de': 'Deutschland' },
        // nationalMode: false,
        // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
        // placeholderNumberType: "MOBILE",
        // preferredCountries: ['cn', 'jp'],
        // separateDialCode: true,
        utilsScript: "../site/utils.js",
    });
</script>

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
<script src="js/jquery.js"></script>
<script src="../site/jquery.js"></script>
<script src="../site/jquery.stellar.min.js"></script>
<script src="../site/wow.min.js"></script>
<script src="../site/owl.carousel.min.js"></script>
<script src="../site/jquery.magnific-popup.min.js"></script>
<script src="../site/smoothscroll.js"></script>
<script src="../site/custom.js"></script>


<!--JS-->


</body>
</html>