<?php

try {
    $info = $Features->prelim_install();
} catch (Exception $ex) {
    $Features->clean_temp_folder();

    notify("admin", "failure", $ex->getMessage());

    if ($this->developer_mode() == true) {
        Pre($Features->developer_message);
    }

    die();
}

?>

<input type="hidden" name="filename" value="<?=$info['filename']?>" />

<div class='form-group'>
    <label class='control-label col-3'>Feature Name</label>
    <div class='col-9'><?php echo $info['name']; ?></div>
</div>

<div class='form-group'>
    <label class='control-label col-3'>Feature Folder</label>
    <div class='col-9'><?php echo $info['alias']; ?></div>
</div>

<hr class='form-split'>

<div class='form-group'>
    <label class='control-label col-4'>Total Feature Files</label>
    <div class='col-8'><?php echo $info['files']; ?></div>
</div>
<div class='form-group'>
    <label class='control-label col-4'>Feature File Size</label>
    <div class='col-8'><?php echo $info['filesize']; ?></div>
</div>

<hr class='form-split'>

<div class='form-group'>
    <label class='control-label col-4'>Database Changes</label>
    <div class='col-8'><?php echo $info['db_changes']; ?></div>
</div>

<?php
if ($info['version'] != "" || $info['notes'] != "") echo "<hr class='form-split'>";

if ($info['version'] != ""):
?>
<div class='form-group'>
    <label class='control-label col-3'>Feature Version</label>
    <div class='col-9'><?php echo $info['version']; ?></div>
</div>
<?php
endif;
if ($info['notes'] != ""):
?>
<div class='form-group'>
    <label class='control-label col-3'>Feature Notes</label>
    <div class='col-9'>
        <ul class='feature-notes'>
        <?php
        foreach ($info['notes'] as $version => $notes) {
            echo '<li>';
            echo '<span class="version">'.$version.'</span>';
            if (is_array($notes)) {
                echo '<ul>';
                foreach ($notes as $note) echo '<li>'.$note.'</li>';
                echo '</ul>';
            } else echo $note;
            echo '</li>';
        }
        ?>
        </ul>
    </div>
</div>
<?php
endif;

if ($info['author']['author'] != "" || $info['author']['alias'] != "" || $info['author']['email'] != "" || $info['author']['company'] != "") echo "<hr class='form-split'>";

if ($info['author']['author'] != ""):
?>
<div class='form-group'>
    <label class='control-label col-3'>Author Name</label>
    <div class='col-9'><?php echo $info['author']['author']; ?></div>
</div>
<?php
endif;
if ($info['author']['alias'] != ""):
?>
<div class='form-group'>
    <label class='control-label col-3'>Author Alias</label>
    <div class='col-9'><?php echo $info['author']['alias']; ?></div>
</div>
<?php
endif;
if ($info['author']['email'] != ""):
?>
<div class='form-group'>
    <label class='control-label col-3'>Author Email</label>
    <div class='col-9'><?php echo $info['author']['email']; ?></div>
</div>
<?php
endif;
if ($info['author']['company'] != ""):
?>
<div class='form-group'>
    <label class='control-label col-3'>Author Company</label>
    <div class='col-9'><?php echo $info['author']['company']; ?></div>
</div>
<?php
endif;