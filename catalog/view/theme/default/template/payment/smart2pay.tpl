<form id="smart2pay_checkout_form">
    <?php
    /*     if ( ! $checkout_method_id):
    ?>
        <div>
            <p><b><?php echo $trans['label_choose_payment'] ?></b></p>
            <table>
                <?php foreach($methods as $method): ?>
                    <tr>
                        <td>
                            <input type="radio" name="method" class="smart2pay_method_ck" value="<?php echo $method['method_id'] ?>" name="<?php echo $trans[$method['display_name']] ?>">
                        </td>
                        <td>
                            <img src="<?php echo $base_img_url . $method['logo_url'] ?>" style="max-height:40px; max-width:130px;">
                        </td>
                        <td style="padding-left: 10px">
                            <span><?php echo $trans[$method['description']] ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php
        endif;*/
    ?>

    <div class="buttons">
        <div class="pull-right">
            <input type="submit" value="<?php echo $trans['btn_confirm_payment_method']; ?>" class="btn btn-primary" />
        </div>
    </div>
</form>

<script>
    (function(){
        var payURL = '<?php 
        
        $server_base = null;
    	if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
			$server_base = HTTP_SERVER;
		} else {
			$server_base = HTTPS_SERVER;
		}
        
        echo $server_base; ?>index.php?route=payment/smart2pay/pay&method=';
        var checkoutMethodID = '<?php echo $checkout_method_id; ?>';

        $('#smart2pay_checkout_form').on('submit', function(event){
            event.preventDefault();
            if ( ! checkoutMethodID) {
                var methodID = $('input[name=method]:checked', $(this)).val();
                if (methodID == undefined) {
                    alert('<?php echo $trans['label_choose_payment_alert'] ?>');
                } else {
                    document.location.href = payURL + methodID;
                }
            } else {
                document.location.href = payURL + checkoutMethodID;
            }
        });
    })()
</script>

