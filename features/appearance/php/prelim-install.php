<?php

try {
    $config = $Appearance->prelim_install();
} catch (Exception $ex) {
    die($Appearance->print_exception($ex));
}

?>
<input type="hidden" name="filename" value="<?=$config['upload']?>" />

<div class='form-group'>
    <label class='control-label col-4'>Total Theme Layouts</label>
    <div class='col-8'><?php echo count($config['layouts']); ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-4'>Total Areas</label>
    <div class='col-8'><?php echo count($config['areas']); ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-4'>Theme Settings</label>
    <div class='col-8'><?php echo $config['settings'] == 'true' ? 'Yes' : 'No'; ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-4'>Navigation Areas</label>
    <div class='col-8'><?php echo count($config['navigation']); ?></div>
</div>

<hr class='form-split'>

<div class='form-group'>
    <label class='control-label col-3'>Theme Folder</label>
    <div class='col-9'><?php echo $config['theme']['folder']; ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-3'>Theme Name</label>
    <div class='col-9'><?php echo $config['theme']['name']; ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-3'>Theme Version</label>
    <div class='col-9'><?php echo $config['theme']['version']; ?></div>
</div>

<hr class='form-split'>

<div class='form-group'>
    <label class='control-label col-4'>Author Name</label>
    <div class='col-8'><?php echo $config['author']['name']; ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-4'>Author Alias</label>
    <div class='col-8'><?php echo $config['author']['alias']; ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-4'>Author Company</label>
    <div class='col-8'><?php echo $config['author']['company']; ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-4'>Author Email</label>
    <div class='col-8'><?php echo $config['author']['email']; ?></div>
</div>