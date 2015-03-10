<?php
    echo $header;
    echo $column_left;
    echo $column_right;

    function smart2payRenderSendForm($data) {
        foreach ($data as $key => $value) {
            echo <<<DATA
                <tr>
                    <td>
                        {$key}
                    </td>
                    <td>
                        <input style="width: 400px" type="text" name="{$key}" value="{$value}"/>
                    </td>
                </tr>
DATA;

        }
    }
?>

<div id="content">
    <div style="min-height: 800px">
        <?php echo $content_top; ?>

        <div style="<?php echo ($settings['smart2pay_debug_form']) ? "display: table;" : "display: none;" ?>">

            <?php if($settings['smart2pay_debug_form']): ?>
                <p><b>Message to hash</b>: <?php echo $string_to_hash; ?></p>
                <p><b>Hash</b>: <?php echo $payment_data['Hash']; ?></p>
            <?php endif; ?>

            <form action="<?php echo  $settings['smart2pay_post_url'] ?>" id="s2pform" method="POST" <?php if($settings['smart2pay_redirect_in_iframe']) echo 'target="merchantIframe"'; ?> >
                <table>
                    <?php echo smart2payRenderSendForm($payment_data); ?>
                    <tr>
                        <td colspan='2'>
                            <input type="submit" value="Submit"/>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>


    <div id="iframe-container" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; z-index: 1000">
        <div style="position: relative; width: 100%; height: 100%;">
            <div style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background: #333; opacity: 0.5; filter:alpha(opacity=50)"></div>
            <div style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%;">
                <div style="display: table; margin: 0px auto; margin-top: 50px;">
                    <?php if($settings['smart2pay_redirect_in_iframe'] && $settings['smart2pay_skip_payment_page'] && (in_array($payment_data['MethodID'], array(1001, 1002)))): ?>
                        <iframe style='border: none; margin: 0px auto; background-color: #ffffff;' id="merchantIframe" name="merchantIframe" src="" width="780" height="500">
                    <?php else:?>
                        <iframe style='border: none; margin: 0px auto; background-color: transparent;' id="merchantIframe" name="merchantIframe" src="" width="900" height="800">
                    <?php endif;?>
                        </iframe>
                </div>
            </div>
        </div>
    </div>

    <script>

        function modalIframe(){
            jQuery("#iframe-container").css({height: jQuery('body').height()});
            jQuery("#iframe-container").appendTo('body');
            jQuery("#iframe-container").show();
        }

        jQuery(document).ready(function() {

            jQuery('#s2pform').submit(function(){
                modalIframe();
            });

            // autosend form if needed
            <?php if(!$settings['smart2pay_debug_form']):?>
                jQuery("#s2pform").submit();
            <?php endif; ?>

            // get/parse smart2pay message
            var onmessage = function(e) {
                console.log(e);
                if(e.data == 'close_HPP') {
                    setTimeout(function() {jQuery('iframe#merchantIframe').remove()}, 300);
                }
                else if (e.data.substring(0, 7) == "height=") {
                    var iframe_height = e.data.substring(7);
                    jQuery('iframe#merchantIframe').attr('height', parseInt(iframe_height)+300);
                    console.log("jQuery('iframe#merchantIframe').attr('height'," + (parseInt(iframe_height)+300) + ");");
                }
                else if (e.data.substring(0, 6) == "width=") {
                    var iframe_width = e.data.substring(6);
                    jQuery('iframe#merchantIframe').attr('width', parseInt(iframe_width)+100);
                    console.log("jQuery('iframe#merchantIframe').attr('width'," + (parseInt(iframe_width)+100) + ");");
                }

                else if  (e.data.substring(0, 12) == "redirectURL="){
                    window.location = e.data.substring(12);
                }
            }

            // set event listener for smart2pay
            if(typeof window.addEventListener != 'undefined') {
                window.addEventListener('message', onmessage, false);
            }
            else if(typeof window.attachEvent != 'undefined') {
                window.attachEvent('onmessage', onmessage);
            }
        });
    </script>
<div>



<?php echo $footer; ?>
