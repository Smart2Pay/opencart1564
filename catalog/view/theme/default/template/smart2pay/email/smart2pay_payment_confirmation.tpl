<html>
<head>
    <title><?php echo $store_url ?> : Order # <?php echo $order_id ?>  - payment confirmation</title>
    <style type='text/css'>
        body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
    </style>
</head>
<body style='background:#F6F6F6; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;'>
<table style='width:600; background: #fff; min-height: 300px; margin: 0px auto; padding: 10px; border:1px solid #E0E0E0;'>
    <tr>
        <td>
            <a href='<?php echo $store_url ?>'><?php echo $store_name ?></a>
            <h1 style='font-size:22px; font-weight:normal; line-height:22px;'>Hello, <?php echo $customer_name ?>!</h1>
            <p>This is the payment confirmation of order # <?php echo $order_id ?>, placed on <?php echo $order_date ?>, using Smart2Pay processor.</p>
            <h2 style='font-size:18px; font-weight:normal; line-height:22px;'>Total paid: <?php echo $order_total ?> <?php echo $order_currency ?></h2>
            <br />
            <p>Thank you!</p>
            <br />
            <p>If you have any questions about your order please contact us at <a href='mailto:<?php echo $suport_email ?>'><?php echo $suport_email ?></a></p>
        </td>
    </tr>
</table>
</body>
</html>
