</div>
<?php require $this->sidebar; ?>
<script src="<?php echo JAVASCRIPT; ?>core/jquery.js" type="text/javascript"></script>
<script src="<?php echo JAVASCRIPT; ?>core/tether.js" type="text/javascript"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

<script src="<?php echo JAVASCRIPT; ?>Main.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        data: {
            'xhr_csrf_token' : '<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>',
            'xhr_true' : 'true',
            'xhr_is_mobile' : <?php $mobile = new Mobile(); if($mobile->isMobile()){echo "true";}else{echo "false";}; ?>
        }
    });
</script>
<?php
if(isset($this->javascript) && $this->javascript != "") {
    ?>
    <script src="<?php echo JAVASCRIPT; ?><?php echo $this->javascript; ?>.js" type="text/javascript"></script>
    <?php
}
?>
</body>
</html>