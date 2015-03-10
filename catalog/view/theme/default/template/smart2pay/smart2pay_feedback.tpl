<?php
    echo $header;
    echo $column_left;
    echo $column_right;
?>

<div id="content">
    <?php echo $content_top; ?>
    <div class="centered" style="margin-top: 20px; margin-bottom: 20px;">
        <?php echo $feedback; ?>
        <div class="buttons centered" style="text-align: center">
            <a class="btn btn-primary" href="<?php echo $redirect; ?>">Continue</a>
        </div>
        <meta HTTP-EQUIV="refresh" CONTENT="5; URL=<?php echo $redirect; ?>">
    </div>
</div>
