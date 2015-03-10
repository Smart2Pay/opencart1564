<?php

    echo $header;
    echo $column_left;

    function renderSmart2PayFormElements($elements, $errors) {
        foreach($elements as $name => $element) {
            echo '<div class="form-group' . ( (isset($element['required']) && $element['required']) ? "required" : "" ) . '">';

                echo '<label class="col-sm-2 control-label" for="">' . $element['label'] . "</label>";
                echo '<div class="col-sm-10">';
                    switch($element['type']) {
                        case 'text':
                            echo "<input class='form-control' style='width: 300px' type='text' name='" . $name . "' value='" . $element['value'] . "'/>";
                            break;
                        case 'textarea':
                            echo "<textarea class='form-control' style='width: 300px' name='" . $name . "'>" . $element['value'] . "</textarea>";
                            break;
                        case 'select':
                            echo "<select class='form-control' " . ( (isset($element['multiple']) and $element['multiple']) ? "multiple" : "") . " name='" . $name . "'>";
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
                        echo '<div class="text-danger">' . $errors[$name] . '</div>';
                    }
                echo "</div>";
            echo "</div>";
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
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form" data-toggle="tooltip" title="<?php echo $btn_text_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $btn_text_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
                    <?php
                        renderSmart2PayFormElements($form_elements, $error);
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php echo $footer; ?> 