<div class='admin-tabs'><?php echo $Navigation->navigation_tabs(FILE); ?></div>

<?php

// Try to get the link information
try { $link = $Navigation->get_link(filter_input(INPUT_GET, 'id')); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->getMessage())); }

?>

<div id='remove-result' style='margin-top: 15px;'></div>

<form class='form-horizontal' id='remove-link-form' style='width: 500px;'>
    <div class='col-12'>
        <input type='hidden' name='id' value='<?php echo $link['id']; ?>'>
        Are you sure you want to remove the link <b><?php echo $link['text']; ?></b>?<br>
        <span style='color: #AAA; font-size: .8em;'>(<?php echo $link['path']; ?>)</span><br><br>
        Removing a link cannot be undone.
    </div>
    
    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Remove</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_navigation_tab');
    admin_window_run_on_load('remove_link');
</script>