$jq = jQuery.noConflict();

$jq().ready(function() {
  $jq('#pass1').val(NeverEmailPasswords.password);
  $jq('#pass2').val(NeverEmailPasswords.password);
  $jq('#send_password').parent().parent().parent().hide();
})
