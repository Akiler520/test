<?php
$data = '{"id":196,"name":"\'\u6d6a\u6f2b\u8499\u7279\u5229\u6d77\u6e7e\u53ca\u5341\u4e03\u91cc\u9ec4\u91d1\u6d77\u6e7e\u53ca\u5341\u4e03\u91cc\u9ec4\u91d1\u6d77\u5cb8\u6e29\u99a8\u54c1\u8d28\u4e4b\u65c5\u6d6a\u6f2b\u8499\u7279\u5229\u6d77\u6e7e\u53ca\u5341\u4e03\u91cc\u9ec4\u91d1\u6d77\u5cb8\u6e29\u99a8\u54c1\u8d28\u4e4b\u65c5\'","multi_days_weight":-10}';

$productInfo = json_decode($data, true);

$keys 	= array_keys($productInfo);
$values = array_values($productInfo);
//$sql 	= 'replace into tff_product_new (`' . implode('`,`', $keys) . '`) values (' . implode(',', $values) . ')';
$sql = 'SELECT * FROM tff_product_new WHERE duration<=86400 AND product_entity_type<>3 AND category_id NOT IN (1143,1034) AND category_id=327 ORDER BY `price` ASC,weight DESC LIMIT 0,12 OPTION max_matches=9999';

$type = "mysqlcc";
if($type == "mysql"){
    $conn = mysqli_connect("192.168.100.200",'','','','9306');

    var_dump($sql);
    $result = mysqli_query($conn, $sql);

    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
}else{
    $dsn = "mysql:host=192.168.100.200;port:9306";
    $PDO = new PDO($dsn, '', '');

    $result = $PDO->query($sql);

//    $products = $PDO->
    while($row = $result -> fetch()){
        print_r($row);
    }
}

echo "<pre>";var_dump($products);exit;