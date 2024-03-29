<div id='user-result'></div>

<form class='form-horizontal col-10' id='user-form'>
    <h2 class='form-header' style='margin-top: 0px;'>Your Name</h2>

    <div class='form-group'>
        <label class='control-label col-3' for='first-name'>First Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' id='first-name' name='firstname' value='<?php echo $Theamus->User->user['firstname']; ?>'>
            <p class='help-block'>Probably the first part of your name.</p>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='last-name'>Last Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' id='last-name' name='lastname' value='<?php echo $Theamus->User->user['lastname']; ?>'>
            <p class='help-block'>Most definitely the last part of your name.</p>
        </div>
    </div>

    <h2 class='form-header'>Other Information</h2>

    <div class='form-group'>
        <label class='control-label col-3' for='gender'>Gender</label>
        <div class='col-9'>
            <select class='form-control' id='gender' name='gender'>
                <?php
                $genders = array('m' => 'Male', 'f' => 'Female');
                foreach ($genders as $key=>$val) {
                    $selected = $Theamus->User->user['gender'] == $key ? 'selected' : '';
                    echo '<option value=\''.$key.'\' '.$selected.'>'.$val.'</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3'>Birthday</label>
        <div class='col-9'>
            <?php $birthday = explode('-', $Theamus->User->user['birthday']); ?>
            <select class='form-control form-control-inline' name='bday_m'>
                <?php
                $months = array(
                    '1' => 'January',
                    '2' => 'February',
                    '3' => 'March',
                    '4' => 'April',
                    '5' => 'May',
                    '6' => 'June',
                    '7' => 'July',
                    '8' => 'August',
                    '9' => 'September',
                    '10' => 'October',
                    '11' => 'November',
                    '12' => 'December'
                );

                for ($i=1; $i<=12; $i++) {
                    $selected = $i == $birthday[1] ? 'selected' : '';
                    echo '<option '.$selected.' value=\''.$i.'\'>'.$months[$i].'</option>';
                }
                ?>
            </select>

            <select class='form-control form-control-inline' name='bday_d'>
                <?php
                for ($i=1; $i<=31; $i++) {
                    $selected = $i == $birthday[2] ? 'selected' : '';
                    echo '<option '.$selected.' value=\''.$i.'\'>'.$i.'</option>';
                }
                ?>
            </select>

            <select class='form-control form-control-inline' name='bday_y'>
                <?php
                for ($i=2014; $i>=1940; $i--) {
                    $selected = $i == $birthday[0] ? 'selected' : '';
                    echo '<option '.$selected.' value=\''.$i.'\'>'.$i.'</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group' style='text-align: right;'>
        <button type='submit' class='btn btn-success'>Save Personal Information</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() { edit_personal(); });
</script>