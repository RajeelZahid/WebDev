<?php

require('seller.top.inc.php');

$msg = '';
if (isset($_POST['submit'])) {
    $productId = get_safe_values($con, $_POST['productId']);
    $productId_ar = str_split($productId, 1);
    $productName = get_safe_values($con, $_POST['productName']);
    $productCategorie = get_safe_values($con, $_POST['productCategorie']);
    $productWeight = get_safe_values($con, $_POST['productWeight']);
    $productWeight_ar = str_split($productWeight, 1);
    $productPrice = get_safe_values($con, $_POST['productPrice']);
    $productPrice_ar = str_split($productPrice, 1);
    $productDescrip = get_safe_values($con, $_POST['productDescrip']);

    $invalidWeight = check_numeric_input($productWeight_ar, 2);
    $invalidPrice = check_numeric_input($productPrice_ar, 6);
    $invalidProductId = check_alphanum_input($productId_ar, 10);
    $productIdTaken = isTaken('productId', $productId, 'productdetail');
    $imageFileMsg = isImageFileValid('productImage');
    $errors = 0;

    if ($invalidPrice == 1) {
        $errors++;
        $msg .= '<br>Invalid Price';
    }

    if ($invalidWeight == 1) {
        $errors++;
        $msg = $msg . '<br>Invalid Weight';
    }

    if ($invalidProductId == 1) {
        $errors++;
        $msg = $msg . '<br>Invalid Product ID';
    }

    if ($productIdTaken == 1) {
        $errors++;
        $msg = $msg . '<br>Product Id already taken';
    }

    if ($imageFileMsg != 'valid') {
        $errors++;
        $msg = $msg . '<br>' . $imageFileMsg;
    }

    if ($errors == 0) {
        $loggedOnEmail = $_SESSION['USERNAME'];
        $rowBusinessVendor = getRows('email', $loggedOnEmail, 'businessvendordetail');
        $id = mktime() . 'PROD' . $productId;
        date_default_timezone_set('Asia/Karachi');
        $creatingDate = date('Y') . '-' . date('m') . '-' . date('d');
        $folderName = $rowBusinessVendor['folderName'];
        $fileName = 'file' . $productId;
        $businessName = $rowBusinessVendor['businessName'];
        $businessVendorId = $rowBusinessVendor['id'];
        $sql = "insert into productdetail(id,productId,productName,folderName,fileName,businessVendorId,businessName,
                productCategorie,productWeight,productPrice,creatingDate,prodDescrip)
                values('$id','$productId','$productName','$folderName','$fileName','$businessVendorId','$businessName'
                ,'$productCategorie','$productWeight','$productPrice','$creatingDate','$productDescrip')";
        $sql2 = "insert into productdetail(productId,businessId,status,actionTakenBy)
                 values('$productId','$businessVendorId','none','none')";
        mysqli_query($con, $sql);
        mysqli_query($con, $sql2);

        $file_tmpname = $_FILES['productImage']['tmp_name'];
        move_uploaded_file($file_tmpname, '../db/' . $folderName . '/' . $fileName);
        header('location:sellerdashboard.php');
        die();
    }
}

?>


<!-- Begin Page Content -->
<html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../site/bootstrap.min.css">
    <link rel="stylesheet" href="../site/styy.css">
    <link rel="stylesheet" href="../site/css/font-awesome.min.css">


    <title>Add Product | FooDino</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&display=swap"
          rel="stylesheet">
    <!--fontawesome-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
</head>

<body>
<div>
    <div class="container-fluid px-lg-4">
        <div class="row">
            <div class="col-md-12 mt-lg-4 mt-4">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-download fa-sm text-white-50"></i>
                        Generate Report</a>
                </div>
            </div>
        </div>
        <div>
            <div class="jumbotron">
                <div class="row w-100">
                    <div class="col-md-3">
                        <div class="card border-info mx-sm-1 p-3">
                            <div class="card border-info shadow text-info p-3 my-card"><span><i
                                            class="fab fa-first-order"></i></span></div>
                            <div class="text-info text-center mt-3"><h4>Orders</h4></div>
                            <div class="text-info text-center mt-2"><h1>24</h1></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success mx-sm-1 p-3">
                            <div class="card border-success shadow text-success p-3 my-card"><span
                                        class="fa fa-credit-card" aria-hidden="true"></span></div>
                            <div class="text-success text-center mt-3"><h4>Payment</h4></div>
                            <div class="text-success text-center mt-2"><h1>9</h1></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger mx-sm-1 p-3">
                            <div class="card border-danger shadow text-danger p-3 my-card"><span>  <i
                                            class="fab fa-product-hunt"></i> </span></div>
                            <div class="text-danger text-center mt-3"><h4>Products</h4></div>
                            <div class="text-danger text-center mt-2"><h1>36</h1></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning mx-sm-1 p-3">
                            <div class="card border-warning shadow text-warning p-3 my-card"><span
                                        class="fa fa-map-marker" aria-hidden="true"></span></div>
                            <div class="text-warning text-center mt-3"><h4>Location</h4></div>
                            <div class="text-warning text-center mt-2"><h1>3</h1></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="container">
            <form class="" method="post" enctype="multipart/form-data">
                <fieldset>

                    <!-- Form Name -->
                    <legend>ADD PRODUCTS</legend>

                    <!-- Text input-->
                    <div class="form-group ">
                        <label class="col-md-4 control-label" for="praddproduct.poduct_id">PRODUCT ID</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <input id="product_id" name="productId" minlength="6" maxlength="10"
                                   placeholder="PRODUCT ID" class="form-control input-md" required="" type="text">

                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="product_name">PRODUCT NAME</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <input id="product_name" name="productName" placeholder="PRODUCT NAME"
                                   class="form-control input-md" required="" type="text">

                        </div>
                    </div>

                    <!-- Select Basic -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="product_categorie">PRODUCT
                            CATEGORY</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <select id="product_categorie" name="productCategorie"
                                    class="form-control">
                                <option>Main</option>
                                <option>Sweet</option>
                                <option>Starter</option>
                            </select>
                        </div>
                    </div>


                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="product_weight">PRODUCT
                            WEIGHT</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <input id="product_weight" minlength="1" maxlength="2" name="productWeight"
                                   placeholder="PRODUCT WEIGHT (in KG)" class="form-control input-md"
                                   required type="text">

                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="productPrice">PRODUCT PRICE</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <input id="productPrice" name="productPrice" minlength="3" maxlength="6"
                                   placeholder="PRODUCT PRICE" class="form-control input-md" required="" type="text">

                        </div>
                    </div>

                    <!-- Textarea -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="product_description">PRODUCT
                            DESCRIPTION</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <textarea class="form-control" id="product_description"
                                      name="productDescrip"></textarea>
                        </div>
                    </div>

                    <!-- File Button -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="productImage">main_image</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <input id="productImage" name="productImage" class="input-file" type="file">
                        </div>
                    </div>


                    <!-- Button -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="submit">Submit</label>
                        <div class="col-md-6 d-sm-inline-block">
                            <button id="submit" name="submit" class="btn btn-primary">Post
                            </button>
                        </div>
                    </div>

                    <?php echo $msg; ?>

                </fieldset>
            </form>
        </section>


        <!-- /.container-fluid -->

    </div>
</div>
</body>
</html>