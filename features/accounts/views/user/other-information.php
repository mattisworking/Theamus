<form class='form-horizontal col-10' id='user-form'>
    <h2 class='form-header'>General Information</h2>

    <div class='form-group'>
        <label class='control-label col-3'>Member Since</label>
        <div class='col-9'>
            <p class='form-control-static'><?php echo date('F jS, Y', strtotime($Theamus->User->user['created'])); ?></p>
            <p class='help-block'>Thank you!</p>
        </div>
    </div>

    <h2 class='form-header'>Other Information</h2>

    <div class='form-group'>
        <label class='control-label col-3'>Associated Groups</label>
        <div class='col-9'>
            <p class='form-control-static'>
                <?php
                foreach (explode(',', $Theamus->User->user['groups']) as $group) {
                    echo ucwords(str_replace('_', ' ', $group)). '<br>';
                }
                ?>
            </p>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3'>Accessible Features</label>
        <div class='col-9'>
            <p class='form-control-static'>
                <?php
                foreach (explode(',', $Theamus->User->user['groups']) as $group) {
                    $query = $Theamus->DB->select_from_table(
                        $Theamus->DB->system_table('features'),
                            array('name'),
                            array('operator'   => '',
                                  'conditions' => array('groups' => $group)));

                    if ($Theamus->DB->count_rows($query) > 0) {
                        foreach ($Theamus->DB->fetch_rows($query) as $feature) echo $feature['name'].'<br>';
                    }
                }
                ?>
            </p>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#user-form').submit(function(e) { e.preventDefault(); });
    });
</script>