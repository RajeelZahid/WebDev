<?php
require('admin.top.inc.php');
$user = $_SESSION['USERNAME'];
$msg = '';

$error = 0;
$bisName = 'Vendor Detail';
$bisProds = [];

global $con;


if (array_key_exists('ifb', $_GET) && $_GET['ifb'] != '') {
    $fid = $_GET['ifb'];
    $contain = isContain('feedbackId', $fid, 'orderfeedback');
    echo $contain;
    if ($contain == 1) {
        echo 'o';
        $bid = getEntry('orderfeedback', 'businessId', 'feedbackId', $fid);
        echo $bid;
        $sql = "update orderfeedback set visible='no' where businessId='$bid'";
        mysqli_query($con, $sql);
        header('location:vendordetail.php?vend=' . $bid);
    }
}
if (array_key_exists('vfb', $_GET) && $_GET['vfb'] != '') {
    $fid = $_GET['vfb'];
    if (isContain('feedbackId', $fid, 'orderfeedback') == 1) {
        $bid = getEntry('orderfeedback', 'businessId', 'feedbackId', $fid);
        $sql = "update orderfeedback set visible='yes' where businessId='$bid'";
        mysqli_query($con, $sql);
        header('location:vendordetail.php?vend=' . $bid);
    }
}

if (array_key_exists('vendordetail_VENDORSELECTED', $_SESSION)) {
    $bisID = $_SESSION['vendordetail_VENDORSELECTED'];
    if (array_key_exists('disapprove', $_GET)) {
        $pid = $_GET['disapprove'];
        $check1 = isExistWRTothField('productId', $pid, 'status', 'approved', 'products');
        $check2 = isExistWRTothField('productId', $pid, 'status', 'none', 'products');
        if ($check1 == 1 || $check2 == 1) {
            $sql = "update products set `status`='disapproved' , `actionTakenBy`='$user'
                where `productId`='$pid'";
            mysqli_query($con, $sql);
        }
        header('location:vendordetail.php?vend=' . $bisID);
    } else if (array_key_exists('approve', $_GET)) {
        $pid = $_GET['approve'];
        $check1 = isExistWRTothField('productId', $pid, 'status', 'disapproved', 'products');
        $check2 = isExistWRTothField('productId', $pid, 'status', 'none', 'products');
        if ($check1 == 1 || $check2 == 1) {
            $sql = "update products set `status`='approved', `actionTakenBy`='$user'
                where `productId`='$pid'";
            mysqli_query($con, $sql);
        }
        header('location:vendordetail.php?vend=' . $bisID);
    } else if (array_key_exists('delete', $_GET)) {
        $pid = $_GET['delete'];
        $check = isExistWRTothField('productId', $pid, 'status', 'disapproved', 'products');
        if ($check == 1) {
            $sql = "delete from products where `productId`='$pid'";
            mysqli_query($con, $sql);
            $sql = "delete from productdetail where `productId`='$pid'";
            mysqli_query($con, $sql);
        }
        header('location:vendordetail.php?vend=' . $bisID);
    }
}


if (array_key_exists('vend', $_GET) && $_GET['vend'] != '') {
    $bid = $_GET['vend'];
    if (isContain('id', $bid, 'businessvendordetail') == 0) {
        $error = 1;
        $msg = 'No Vendor found for this ID..!';
        unset($_SESSION['vendordetail_VENDORSELECTED']);
    } else {
        $_SESSION['vendordetail_VENDORSELECTED'] = $bid;
        $bisDetail = getRows('id', $bid, 'businessvendordetail');
        if (isContain('businessId', $bid, 'orderfeedback') != 0) {
            $feedbackDetail = getRowsOfSameEntry('businessId', $bid, 'orderfeedback');
        } else {
            $feedbackDetail = [];
        }
        $bisName = ucwords($bisDetail['businessName']);
        $bisProds = getRowsOfSameEntry('businessVendorId', $bid, 'productdetail');
        for ($i = 0; $i < count($bisProds); $i++) {
            $bisProds[$i]['status'] = getEntry('products', 'status', 'productId', $bisProds[$i]['productId']);
        }
        shuffle($bisProds);
//        pr($bisProds);
    }
} else {
    $error = 1;
    $msg = 'No Vendor Selected..!';
}


?>
<!doctype html>
<html lang="en">

<head>
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
-->
    <link rel="stylesheet" href="../site/style.css">
    <link rel="stylesheet" href="../site/css.css">
    <link rel="stylesheet" href="../site/css/font-awesome.min.css">
    <link rel="stylesheet" href="../site/bootstrap.min.css">
    <title><?php echo $bisName . ' | FooDino' ?></title>

    <!-- js -->
    <script src="../files/includehtml.js"></script>

</head>

<body>
<div include-html="admin_header.php"></div>

<script>
    includeHTML();
</script>

<?php if ($error == 0) { ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div id="content" class="content content-full-width">
                    <!-- begin profile -->
                    <div class="profile">
                        <div class="profile-header">
                            <!-- BEGIN profile-header-cover -->
                            <div class="profile-header-cover"></div>
                            <!-- END profile-header-cover -->
                            <!-- BEGIN profile-header-content -->
                            <div class="profile-header-content">
                                <!-- BEGIN profile-header-img -->
                                <div class="profile-header-img">
                                    <?php
                                    $folderName = $bisDetail['folderName'];
                                    $fileName = str_replace('folder', 'file', $folderName);
                                    ?>
                                    <img src=<?php echo '../db/' . $folderName . '/' . $fileName ?> alt="">
                                </div>
                                <!-- END profile-header-img -->
                                <!-- BEGIN profile-header-info -->
                                <div class="profile-header-info">
                                    <h2 class="m-t-10 m-b-5"><?php echo ucwords($bisDetail['businessName']) ?></h2>
                                    <h4 class="m-t-10 m-b-5"><?php echo ucwords($bisDetail['vertical']) ?></h4>
                                    <a href="#" class="btn btn-sm btn-info mb-2">Follow</a>
                                </div>
                                <!-- END profile-header-info -->
                            </div>
                            <!-- END profile-header-content
                             BEGIN profile-header-tab -->

                            <!--END profile-header-tab -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="text-center card-box">
                        <div class="member-card">
                            <!--  <div class="thumb-xl member-thumb m-b-10 center-block">
                                   <img src="https://bootdey.com/img/Content/avatar/avatar6.png" class="img-circle img-thumbnail" alt="profile-image">
                               </div>

                               <div class="">
                                   <h4 class="m-b-5">John J. Doe</h4>
                                   <p class="text-muted">@johndoe</p>
                               </div>

                               <button type="button" class="btn btn-success btn-sm w-sm waves-effect m-t-10 waves-light">Follow</button>
                               <button type="button" class="btn btn-danger btn-sm w-sm waves-effect m-t-10 waves-light">Message</button>-->
                            <h3>Profile</h3>
                            <div class="text-left m-t-40">

                                <p class="text-muted font-13"><strong>Full Name :</strong> <span class="m-l-15">
                                    <?php echo ucwords($bisDetail['vendorName']) ?>
                                </span>
                                </p>
                                <p class="text-muted font-13"><strong>Mobile :</strong><span
                                            class="m-l-15">
                                    <?php echo $bisDetail['contactno'] ?>
                                </span></p>
                                <p class="text-muted font-13"><strong>Email :</strong> <span class="m-l-15">
                                    <?php echo $bisDetail['email'] ?>
                                </span>
                                </p>
                                <p class="text-muted font-13"><strong>Location :</strong> <span
                                            class="m-l-15">
                                    <?php echo ucwords($bisDetail['address']) ?>
                                </span>
                                </p>
                                <a href=<?php echo "businessMap.php?lat=" . $bisDetail['latitude'] . "&long=" . $bisDetail['longitude'] ?>>See
                                    Map
                                </a>
                            </div>

                            <!--  <ul class="social-links list-inline m-t-30">
                                   <li>
                                       <a title="" data-placement="top" data-toggle="tooltip" class="tooltips" href="" data-original-title="Facebook"><i class="fa fa-facebook"></i></a>
                                   </li>
                                   <li>
                                       <a title="" data-placement="top" data-toggle="tooltip" class="tooltips" href="" data-original-title="Twitter"><i class="fa fa-twitter"></i></a>
                                   </li>
                                   <li>
                                       <a title="" data-placement="top" data-toggle="tooltip" class="tooltips" href="" data-original-title="Skype"><i class="fa fa-skype"></i></a>
                                   </li>
                               </ul>-->
                        </div>
                    </div> <!--end card-box-->

                    <div class="card-box">
                        <h4 class="m-t-0 m-b-20 header-title">Ratings</h4>
                        <div class="p-b-10">
                            <div class="progress progress-sm">
                                <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="90"
                                     aria-valuemin="0" aria-valuemax="100" style="width: 90%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->


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
                                    <a href="#products" onclick="prods()" data-toggle="tab" aria-expanded="true">
                                        <span class="visible-xs"><i class="fa fa-book"></i></span>
                                        <span class="hidden-xs">Products</span>
                                    </a>
                                </li>
                                <li class="notactive" id="fb">
                                    <a href="#feedbacks" onclick="fbacks()" data-toggle="tab" aria-expanded="false">
                                        <span class="visible-xs"><i class="fa fa-star"></i></span>
                                        <span class="hidden-xs">Feedbacks</span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="products">
                                    <div class="row">
                                        <?php
                                        for ($i = 0; $i < count($bisProds); $i++) {
                                            echo '
                                    <div class="col-sm-4">
                                        <div class="gal-detail thumb">
                                            <a href="#" class="image-popup">
                                                <img src="../db/' . $bisProds[$i]['folderName'] . '/' . $bisProds[$i]['fileName'] . '"
                                                     class="thumb-img"
                                                     alt="">
                                            </a>
                                            <a href="detailsprod.php?prod=' . $bisProds[$i]['productId'] . '">
                                                <h5 class="text-center">' . ucwords($bisProds[$i]['productName']) . '</h5>
                                            </a>
                                            <div class="ga-border"></div>
                                            <h6  align="center">' . $bisProds[$i]['productPrice'] .
                                                ' Rs for ' . $bisProds[$i]['productWeight'] . 'Kg</h6>';
                                            if ($bisProds[$i]['status'] == 'approved') {
                                                echo '<h6  align="center">This is Approved Product</h6>
                                                    <a href = "?disapprove=' . $bisProds[$i]["productId"] . '" ><button type = "submit" class="btn btn-primary " > Disapprove</button ></a >';
                                            }
                                            if ($bisProds[$i]['status'] == 'disapproved') {
                                                echo '<h6  align="center">This is Disapproved Product</h6>
                                                    <a href = "?approve=' . $bisProds[$i]["productId"] . '" ><button type = "submit" class="btn btn-primary " >Approve</button ></a >
                                                    <a href = "?delete=' . $bisProds[$i]["productId"] . '" ><button type = "submit" class="btn btn-primary " >Delete from DB</button ></a >';
                                            }
                                            if ($bisProds[$i]['status'] == 'none') {
                                                echo '<h6  align="center">No active taken on this Product</h6>
                                                    <a href = "?approve=' . $bisProds[$i]["productId"] . '" ><button type = "submit" class="btn btn-primary " > Approve</button ></a >
                                                    <a href = "?disapprove=' . $bisProds[$i]["productId"] . '" ><button type = "submit" class="btn btn-primary " > Disapprove</button ></a >';
                                            }
                                            echo '
                    </div >
                                    </div > ';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="feedbacks">
                                    <div class="m-t-30">
                                        <?php

                                        if (count($feedbackDetail) > 0) {
                                            shuffle($feedbackDetail);
                                            for ($i = 0; $i < count($feedbackDetail); $i++) {
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
                                                for ($st = 1; $st <= intval($feedbackDetail[$i]['rating']); $st++) {
                                                    echo '<span class="float-right"><i
                                                                    class=" fa fa-star-o"></i></span>';
                                                }
                                                for ($st = 5; $st > intval($feedbackDetail[$i]['rating']); $st--) {
                                                    echo '<span class="float-right"><i
                                                                    class="text-warning fa fa-star"></i></span>';
                                                }
                                                echo '
                                                    </p>
                                                    <div class="clearfix"></div>
                                                    <p>' . $feedbackDetail[$i]['feedback'] . '
                                                    </p>
                                                </div>';
                                                if ($feedbackDetail[$i]['visible'] == 'yes') {
                                                    echo '<a href = "?ifb=' . $feedbackDetail[$i]['feedbackId'] . '" >
                                                    <button type = "submit" class="btn btn-primary ">Invisible</button>
                                                              </a>';
                                                } else if ($feedbackDetail[$i]['visible'] == 'no') {
                                                    echo '<a href = "?vfb=' . $feedbackDetail[$i]['feedbackId'] . '" >
                                                    <button type = "submit" class="btn btn-primary ">Visible</button>
                                                              </a>';
                                                }

                                                echo '
                                                     </div>
                                        </div>';
                                            }
                                        } else {
                                            echo '<h3 align="center"> No Feedbacks Yet</h3>';
                                        }
                                        ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
        </div>
        <!-- end row -->

    </div>
    </div>
<?php } else echo '<h3 align = "center" > ' . $msg . '
<br ><br ><br ><br ><br ><br ><br ><br ><br ><br ><br ><br ><br >
</h3 > ';
?>
<br><br>
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
<script src="js/jquery.js"></script>
<script src="../site/jquery.js"></script>
<script src="../site/jquery.stellar.min.js"></script>
<script src="../site/wow.min.js"></script>
<script src="../site/owl.carousel.min.js"></script>
<script src="../site/jquery.magnific-popup.min.js"></script>
<script src="../site/smoothscroll.js"></script>
<script src="../site/custom.js"></script>

</body>
