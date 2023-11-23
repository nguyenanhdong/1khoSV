<div class="list-controler-action">
    <h3>Danh sách nhóm quyền</h3>
    <form method="POST" action="">
        <?php
            if( !empty($listRole) ){
            $i = 0;
            ksort($listRole);
            foreach($listRole as $roleName=>$checked){
                $i++;
        ?>
            <div class="rowController">
                <ul class="list-action" style="padding-left:6px">
                    <li style="width: 100%;" class="li-action <?= $checked == 'checked' ? 'active' : '' ?>">
                        <label class="">
                            <input <?= $checked == 'checked' ? 'checked="true"' : '' ?> class="input_action_role " id="action_<?= $i ?>" type="checkbox" name="actionsAssign[]" value="<?= $roleName ?>" />
                            <?= $roleName ?>
                            
                        </label>
                        <a target="_blank" href="/role/view/?id=<?= $roleName ?>">
                            <i class="fal fa-search"></i>
                        </a>
                    </li>
                </ul>
            </div>
        <?php
            }
        ?>
        <button class="btn btn-primary btn-submit-role" type="button"><i style="display:none" class="fal fa-spin fa-spinner"></i> Cập nhật</button>
        <?php }else{
            echo '<p class="text-center" style="margin-top:2em">Chưa có nhóm quyền nào</p>';
        }?>
    </form>
</div>
<div class="list-controler-action">
    <h3>Danh sách quyền</h3>
    <form method="POST" action="">
        <?php
        if( !empty($listControllerAction) ){
            $i = 0;
            ksort($listControllerAction);
            // echo '<pre>';
            // var_dump($listControllerAction);
            // echo '</pre>';

            foreach($listControllerAction as $roleName=>$checked){
                $i++;
        ?>
            <div class="rowController">
                <ul class="list-action" style="padding-left:6px">
                    <li style="width: 100%;" class="li-action <?= $checked == 'checked' ? 'active' : '' ?>">
                        <label class="">
                            <input <?= $checked == 'checked' ? 'checked="true"' : '' ?> class="input_action " id="action_<?= $i ?>" type="checkbox" name="actionsAssign[]" value="<?= $roleName ?>" />
                            <?= $roleName ?>
                            
                        </label>
                        <a target="_blank" href="/permission/view/?id=<?= $roleName ?>">
                            <i class="fal fa-search"></i>
                        </a>
                    </li>
                </ul>
            </div>
        <?php
            }
        ?>
        <button class="btn btn-primary btn-submit" type="button"><i style="display:none" class="fal fa-spin fa-spinner"></i> Cập nhật</button>
        <?php }else{
            echo '<p class="text-center" style="margin-top:2em">Chưa có quyền nào</p>';
        }?>
    </form>
</div>
<style>
    .btn-submit,.btn-submit-role{position:relative;bottom:0}
</style>