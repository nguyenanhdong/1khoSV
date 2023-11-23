<div class="list-controler-action">
    <h3>Task Backend</h3>
    <form method="POST" action="">
        <?php
            $i = 0;
            ksort($listControllerAction);
            
            foreach($listControllerAction as $controller=>$listAction){
                ksort($listAction);
                $i++;
                $controllerShow = str_replace('Admin.','',$controller);
        ?>
            <div class="rowController">
                <span class="controller-toggle"><i class="fal fa-plus"></i> <?= ucfirst(str_replace('Admin.','',$controller) . 'Controller') ?></span>
                <ul class="list-action hide list-action-<?= $i ?>">
                    <?php
                        foreach($listAction as $action=>$checked){
                            $actionName = str_replace('.','/','/' . $controller . '/' . $action);

                    ?>
                    <li class="li-action <?= $checked == 'checked' ? 'active' : '' ?>">
                        <label class="<?= $checked == 'checked' ? 'active' : '' ?>">
                            <input <?= $checked == 'checked' ? 'checked="true"' : '' ?> class="input_action " parentid="list-action-<?= $i ?>" type="checkbox" name="actionsAssign[]" value="<?= strtolower($actionName) ?>" />
                            <?= $controllerShow ?>.<b><?= $action ?></b>
                        </label>
                    </li>
                    <?php
                        }
                    ?>
                </ul>
            </div>
        <?php
            }
        ?>
        <button class="btn btn-primary btn-submit" type="button"><i style="display:none" class="fal fa-spin fa-spinner"></i> Cập nhật</button>
    </form>
</div>