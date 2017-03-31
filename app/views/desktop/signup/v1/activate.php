<div class="a">
    <div class="cover">
        <h1>Welcome!</h1>
        <p>Get ready to experience a new type of social networking</p>
    </div>
</div>
<div class=" col-lg-4 col-md-4 signupCont ac">
    <h1>Hi there <?php echo $this->firstname; ?>!</h1>
    <p>
        Looks like you're ready to join <?php echo SITE_NAME; ?> and experience a whole new way of connecting! Just press the button bellow to activate your account, then you'll be set to go!
    </p><br /><br />
    <button class="btn primaryBTN buttonLong" id="actAcc" data-c="<?php echo $this->code; ?>" data-e="<?php echo $this->email; ?>">Activate my account!</button>
</div>