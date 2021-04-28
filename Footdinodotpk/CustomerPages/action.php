<?php
require('../files/function.inc.php');
global $con;
if (array_key_exists('order_prod_remove', $_POST)) {
    $pid = get_safe_values($con, $_POST['pid']);
    $oid = get_safe_values($con, $_POST['oid']);
    $bid = get_safe_values($con, $_POST['bid']);
    $cid = get_safe_values($con, $_POST['cid']);
    $sql1 = "delete from orderdetail where orderId='$oid' and businessId='$bid' and productId='$pid' and customerId='$cid'";
    $sql2 = "delete from orders where orderId='$oid' and businessId='$bid' and productId='$pid' and customerId='$cid'";
    mysqli_query($con, $sql1);
    mysqli_query($con, $sql2);
    header('location:myorders.php');
    die();
}
if (array_key_exists('order_prod_complete', $_POST)) {
    $pid = get_safe_values($con, $_POST['pid']);
    $oid = get_safe_values($con, $_POST['oid']);
    $bid = get_safe_values($con, $_POST['bid']);
    $cid = get_safe_values($con, $_POST['cid']);
    date_default_timezone_set('Asia/Karachi');
    $updt = date("Y/m/d") . ' ' . date("H:i:s");
    $sql1 = "update orderdetail 
                set orderStatus='Completed', orderStatusId='c', orderStatusUpdatedTime='$updt' 
                where orderId='$oid' and businessId='$bid' and productId='$pid' and customerId='$cid'";
    mysqli_query($con, $sql1);
    header('location:myorders.php');
    die();
}