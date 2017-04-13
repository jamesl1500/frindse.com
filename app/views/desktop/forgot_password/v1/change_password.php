<div class="banner">
    <div class="cover">
        <h1>Change Password</h1>
        <p>Now lets change your password!</p>
    </div>
</div>
<div class=" col-lg-4 col-md-4 fpCont">
    <form action="" method="post" id="cpForm" onsubmit="return false;">
        <div class="responseHold"></div>
        <div class="inputType">
            <label>Enter your new password</label>
            <input type="password" id="password1" placeholder="New Email" />
        </div><br />
        <div class="inputType">
            <label>Re-enter your new password</label>
            <input type="password" id="password2" placeholder="Reenter password" />
        </div><br />
        <div class="inputType">
            <input type="hidden" id="fch" value="<?php echo $this->code; ?>" />
            <input type="hidden" id="feh" value="<?php echo $this->email; ?>" />
            <input type="submit" id="password" value="Change password" class="buttonLong primaryBTN"/>
        </div>
    </form>
</div>