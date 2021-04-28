<?php
if (!isset($_SESSION)) {
    session_start();
}
$_SESSION['CURRENTLY_IN'] = $_SERVER['PHP_SELF'];
require('cust.top.inc.php');
$customerId = $_SESSION['USERNAME'];
$msg = '';

if (array_key_exists('checkout', $_POST)) {
    $fname = get_safe_values($con, $_POST['fname']);
    $lname = get_safe_values($con, $_POST['lname']);
    $customerName = $fname . ' ' . $lname;
    $customerEmail = get_safe_values($con, $_POST['email']);
    $customerPhone = get_safe_values($con, $_POST['contactno']);
    $custShipAddress = get_safe_values($con, $_POST['ship_address']);
    $custBillAddress=get_safe_values($con, $_POST['bill_address']);
    $deliverTime=get_safe_values($con, $_POST['deliverytime']);
    $deliverTime=str_replace('T', ' ', $deliverTime);
    $deliverTime=str_replace('-', '/', $deliverTime);
    $advPayType=get_safe_values($con, $_POST['advPayTb']);
    ?>
    <script>
        if (document.getElementById("same-address").checked === true) {
            <?php $custBillAddress = $custShipAddress; ?>
        }
    </script>
    <?php

    $invalid_contactno = isNumeric($customerPhone, 11);

    $errors = 0;
    if ($invalid_contactno == 1) {
        $errors++;
        $msg .= '<br>Invalid Mobile No.';
    }

    if ($errors == 0) {
        $stoveItemsP = $_SESSION['stoveItems'];
        $stoveItemDetailsP = $_SESSION['stoveItemDetails'];

        date_default_timezone_set('Asia/Karachi');
        $orderAddedTime = date("Y/m/d") . ' ' . date("H:i:s");
        $orderStatus = 'Waiting for vendor response';
        $orderStatusId = 'w';
        $orderStatusUpdatedTime = $orderAddedTime;

        $orderId = time() . str_shuffle($customerId . uniqid());
        $orderId = str_rot13($orderId);
        $orderId=makeUnique('orderId',$orderId,'orders');

        for ($j = 0; $j < count($stoveItemsP); $j++) {
            $productId[$j] = $stoveItemsP[$j]['productId'];
            $productQuantity[$j] = $stoveItemsP[$j]['quantity'];
            $tp = $stoveItemsP[$j]['totalPrice'];
            $productTotalPrice[$j] = $tp + ($tp * 1.5 / 100);
            $businessId[$j] = $stoveItemDetailsP[$j]['businessVendorId'];

            $sql1 = "insert into fyp.orderdetail(orderId, customerId, customerName, customerEmail, customerPhone,shippingAddress, billingAddress,
                        productId, productQuantity, productTotalPrice, businessId, deliveryTime, advancePaymentType,
                        orderAddedTime, orderStatus, orderStatusId,orderStatusUpdatedTime)
                   values('$orderId', '$customerId', '$customerName', '$customerEmail', '$customerPhone','$custShipAddress', '$custBillAddress',
                          '$productId[$j]', '$productQuantity[$j]', '$productTotalPrice[$j]', '$businessId[$j]','$deliverTime','$advPayType',
                          '$orderAddedTime', '$orderStatus', '$orderStatusId','$orderStatusUpdatedTime')";

            $sql2 = "insert into orders(orderId, customerId,productId, businessId)
                   values('$orderId', '$customerId','$productId[$j]','$businessId[$j]')";

            $sql3 = "delete from stove where customerId='$customerId' and productId='$productId[$j]'";
            mysqli_query($con,$sql1);
            mysqli_query($con, $sql2);
            mysqli_query($con, $sql3);
        }
        for ($j = 0; $j < count($stoveItemsP); $j++) {
            unset($_SESSION['stoveItemDetails'][$j]);
            unset($_SESSION['stoveItems'][$j]);
        }

        header('location:myorders.php');
    }
}


if (!array_key_exists('stoveItems', $_SESSION)) {
    header('location:stovepage.php');
    die;
}

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
        header('location:checkout.php');
    } else {
        header('location:checkout.php');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Foodino</title>
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

<?php
if (!array_key_exists('stoveItems', $_SESSION) || count($_SESSION['stoveItems']) == 0) {
    echo '<div class="container">
    <div class="py-5 text-center">

        <h2>You do not have any item to Checkout</h2>
        <a href="userfoodino.php?city=karachi" color="cyan">Add Items Now</a>
    </div>';
    die();
}

?>

<div class="container">
    <div class="py-5 text-center">

        <h2>Checkout</h2>
    </div>
    <div class="row">
        <div class="col-md-4 order-md-2 mb-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Your Stove</span>
                <span class="badge badge-secondary badge-pill"><?php echo count($_SESSION['stoveItems']) ?></span>
            </h4>

            <table class="table-content table-responsive">
                <thead>
                <tr>

                    <th>Product Name</th>
                    <th>Category</th>
                    <th>weight</th>
                    <th>Until Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <tr>

                    <?php
                    $stoveItemDetails = $_SESSION['stoveItemDetails'];
                    $stoveItems = $_SESSION['stoveItems'];

                    for ($i = 0; $i < count($stoveItems); $i++) {
                        echo '
                    <td class="product-name"><a href="detailsprod.php?prod=' . $stoveItemDetails[$i]["productId"] . '">
                    ' . ucfirst($stoveItemDetails[$i]["productName"]) . '</a></td>
                    <td class="product-name"><a >' . ucfirst($stoveItemDetails[$i]["productCategorie"]) . '</a></td>
                    <td class="product-name"><a >' . $stoveItemDetails[$i]["productWeight"] . ' </a></td>
                    <td class="product-price-cart"><span class="amount">' . $stoveItemDetails[$i]["productPrice"] . '</span></td>
                    <td class="product-name"><a >' . $stoveItems[$i]["quantity"] . ' </a></td>
                    <td class="product-subtotal">' . $stoveItems[$i]["totalPrice"] . '</td>
                    <td class="product-remove">

                        <a href="checkout.php?removeItem=' . array_keys($stoveItemDetails)[$i] . '&pid=' . $stoveItems[$i]["productId"] . '">
                        <i class="fa fa-times"></i></a>
                    </td>
                </tr>';
                    }
                    echo '';
                    echo '<h5 class="product-subtotal">Total Amount with shipping and discounts <strong>' . $_SESSION["totalAmount"] . ' Rupees</strong></h5>';
                    ?>

                </tbody>

            </table>
            <br>
            <div class="container">
                <div class="text-center">

                    <a href="userfoodino.php?city=karachi" color="cyan">Want to add more items? Click here</a>
                </div>
            </div>


            <!--   <form class="card p-2">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Promo code">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-secondary">Redeem</button>
                        </div>
                    </div>
                </form>-->
        </div>
        <div class="col-md-8 order-md-1">
            <h4 class="mb-3">Billing address</h4>
            <form class="needs-validation" novalidate="" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName">First name</label>
                        <input type="text" name="fname" class="form-control" id="firstName" placeholder="" value=""
                               required="">
                        <div class="invalid-feedback"> Valid first name is required.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName">Last name</label>
                        <input type="text" name="lname" class="form-control" id="lastName" placeholder="" value=""
                               required="">
                        <div class="invalid-feedback"> Valid last name is required.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phone">Mobile No. </label>
                    <input type="text" class="form-control" id="phone" name="contactno" required minlength="11"
                           maxlength="11" placeholder="03XXXXXXXXX">
                    <div class="invalid-feedback"></div>
                <?php echo '<h6 style="color: #d44444">' .$msg.'</h6>';?>
                </div>
                <div class="mb-3">
                    <label for="email">Email <span class="text-muted">(Optional)</span></label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="you@example.com">
                    <div class="invalid-feedback"> Please enter a valid email address for shipping updates.</div>
                </div>
                <div class="mb-3">
                    <label for="deliverytime">Delivery time</label>
                    <input type="datetime-local" name="deliverytime" class="form-control" id="deliverytime" required>
                    <div class="invalid-feedback"> Please enter a valid date.</div>
                </div>
                <div class="mb-3">
                    <label id="ship_address_label" for="ship_address">Shipping Address</label>
                    <input type="text" name="ship_address" class="form-control" id="ship_address"
                           placeholder="1234 Main St"
                           required="">
                    <div class="invalid-feedback"> Please enter your shipping address.</div>
                </div>
                <div class="custom-control custom-checkbox">
                    <input onclick="makeAddSame()" type="checkbox" class="custom-control-input" id="same-address">
                    <label class="custom-control-label" for="same-address">Shipping address is the same as my billing
                        address</label>
                </div>
                <br>
                <div class="mb-3">
                    <label id="bill_address_label" for="bill_address">Billing Address</label>
                    <input type="text" name="bill_address" class="form-control" id="bill_address"
                           placeholder="1234 Main St" required="">
                    <div class="invalid-feedback"> Please enter your billing address.</div>
                </div>
                <script>
                    function makeAddSame() {
                        if (document.getElementById("same-address").checked === true) {
                            document.getElementById('bill_address').setAttribute('type', 'hidden');
                            document.getElementById('ship_address_label').innerHTML='Shipping Address / Billing Address';
                            document.getElementById('bill_address_label').innerHTML='';
                        }
                        else if (document.getElementById("same-address").checked === false) {
                            document.getElementById('bill_address').setAttribute('type','text');
                            document.getElementById('ship_address_label').innerHTML='Shipping Address';
                            document.getElementById('bill_address_label').innerHTML='Billing Address';
                        }
                    }
                </script>
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="country">Country</label>
                        <select class="custom-select d-block w-100" id="country">
                            <option value="">Choose...</option>
                            <option>United States</option>
                        </select>
                        <div class="invalid-feedback"> Please select a valid country.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="state">State</label>
                        <select class="custom-select d-block w-100" id="state">
                            <option value="">Choose...</option>
                            <option>California</option>
                        </select>
                        <div class="invalid-feedback"> Please provide a valid state.</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="zip">Zip</label>
                        <input type="text" class="form-control" id="zip" placeholder="">
                        <div class="invalid-feedback"> Zip code required.</div>
                    </div>
                </div>
                <hr class="mb-4">

                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="save-info">
                    <label class="custom-control-label" for="save-info">Save this information for next time</label>
                </div>
                <hr class="mb-4">
                <h4 class="mb-3">Advance Payment Type</h4>
                <div class="d-block my-3">
                    <div class="custom-control custom-radio">
                        <input id="credit" onclick="payment1()" name="paymentMethod" type="radio" class="custom-control-input" checked="">
                        <label class="custom-control-label" for="credit">Cash-On-Hand</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input id="debit" onclick="payment2()" name="paymentMethod" type="radio" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="debit">EasyPaisa <small>(service will be available soon)</small></label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input id="debit" onclick="payment3()" name="paymentMethod" type="radio" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="debit">JazzCash <small>(service will be available soon)</small></label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input id="paypal" onclick="payment4()" name="paymentMethod" type="radio" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="paypal">Credit Card <small>(service will be available soon)</small></label>
                    </div>
                </div>

                <input type="hidden" id="advPayTb" name="advPayTb" value="Cash-On-Hand">
                <script>
                    function payment1() {document.getElementById('advPayTb').value="Cash-On-Hand"}
                    function payment2() {document.getElementById('advPayTb').value="Easypaisa"}
                    function payment3() {document.getElementById('advPayTb').value="Jazzcash"}
                    function payment4() {document.getElementById('advPayTb').value="Credit Card"}
                </script>
                <hr class="mb-4">
                <button class="btn btn-primary btn-lg btn-block" name="checkout" type="submit">Fire up the stove /
                    Checkout
                </button>
            </form>

        </div>
    </div>


    <footer class="footer" style="position: sticky;">
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
</div>


</div>


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
<script src="../site/\custom.js"></script>

</body>
</html>