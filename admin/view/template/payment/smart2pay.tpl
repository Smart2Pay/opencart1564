<?php

    echo $header;

    function renderSmart2PayFormElements($elements, $errors) {
        foreach($elements as $name => $element) {
            echo "<tr>";
                echo "<td>" . ( (isset($element['required']) && $element['required']) ? "<span class='required'>*</span>" : "" ) . $element['label'] . "</td>";
                echo "<td>";
                    switch($element['type']) {
                        case 'text':
                            echo "<input style='width: 300px' type='text' name='" . $name . "' value='" . $element['value'] . "'/>";
                            break;
                        case 'textarea':
                            echo "<textarea style='width: 300px' name='" . $name . "'>" . $element['value'] . "</textarea>";
                            break;
                        case 'select':
                            echo "<select " . ( (isset($element['multiple']) and $element['multiple']) ? "multiple" : "") . " name='" . $name . "'>";
                                foreach($element['options'] as $key => $label) {
                                    echo "<option " . (in_array($key, (array) $element['value']) ? "selected='selected'" : "") . " value='" . $key . "'>" . $label . "</option>";
                                }
                            echo "</select>";
                            break;
                        case 'checkbox':
                            if (strstr($name, 'smart2pay_active_methods')) {
                                $indexedOptionsKeys = array_keys($element['options']);
                                $columns = 5;
                                $methodsCount = count($element['options']);
                                $methodsPerColumn = round($methodsCount / $columns);
                                while (($methodsCount % $columns) != 0) {
                                    $methodsCount++;
                                    $methodsPerColumn = $methodsCount / $columns;
                                }
                                echo "<table>
                                          <tr>";
                                              for ($i = 1; $i < $columns + 1; $i++) {
                                                  echo "<td>";
                                                      for ($m = ($i - 1) * $methodsPerColumn; $m < ($i * $methodsPerColumn); $m++) {
                                                          if (isset($indexedOptionsKeys[$m])) {
                                                              echo "<input id='" . $name.$m . "' type='checkbox' value='" . $indexedOptionsKeys[$m] . "' name='" . $name . (count($element['options']) > 1 ? '[]' : '') . "' " . (in_array($indexedOptionsKeys[$m], (array) $element['value']) ? "checked='checked'" : "") . "> <label for='" . $name.$m . "'>" . $element['options'][$indexedOptionsKeys[$m]] . "</label>";
                                                              echo "<br />";
                                                          }
                                                      }
                                                  echo "</td>";
                                              }
                                echo "    </tr>
                                      </table>";
                            } else {
                                foreach($element['options'] as $key => $label) {
                                    echo "<input id='" . $name.$key . "' type='checkbox' value='" . $key . "' name='" . $name . (count($element['options']) > 1 ? '[]' : '') . "' " . (in_array($key, (array) $element['value']) ? "checked='checked'" : "") . "> <label for='" . $name.$key . "'>" . $label . "</label>";
                                }
                            }
                            break;
                    }

                    if (isset($errors[$name])) {
                        echo "<span class='error'>" . $errors[$name] . "</span>";
                    }
                echo "</td>";
            echo "</tr>";
        }
    }

    function renderSmart2PayLogs($logs) {
        if ( ! $logs) {
            echo "There are no logs, yet.";
        } else {
            usort($logs, function($a, $b){
                if ($a['log_id'] == $b['log_id']) {
                    return 0;
                }
                return ($a['log_id'] < $b['log_id']) ? 1 : -1;
            });
            foreach ($logs as $log) {
                echo "[" . $log['log_id'] . "][" . $log['log_created'] . "]" . " " . "[" . $log['log_type'] . "]" . " " . $log['log_data'] . "\r\n";
            }
        }
    }
?>

<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/payment.png" alt="" /> Smart2Pay </h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button">Save</a>
                <a onclick="location = '<?php echo $cancel; ?>';" class="button">Cancel</a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form">
                    <?php
                        renderSmart2PayFormElements($form_elements, $error);
                    ?>
                    <tr>
                        <td style="background: #e3e3c7">Log</td>
                        <td style="background: #e3e3c7">
                            <textarea  style="background: #000000; color: #008000; resize: both; width: 700px; height: 150px;" disabled="disabled"><?php echo renderSmart2PayLogs($logs)  ?></textarea>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<?php echo $footer; ?> 