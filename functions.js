function toggleField(hideObj,showObj) {
  hideObj.disabled=true;
  hideObj.style.display='none';
  showObj.disabled=false;
  showObj.style.display='inline';
  showObj.focus();
}
function checkPass(){                   //used in confirm matching password entries
  var pass1 = document.getElementById('pass1');
  var pass2 = document.getElementById('pass2');
  //var message = document.getElementById('confirmMessage');
  //var button = document.getElementById('submitpwd');
  var goodColor = "#66cc66";
  var badColor = "#ff6666";
  if((pass1.value != '') && (pass1.value == pass2.value)){
    pass2.style.backgroundColor = goodColor;
    //message.style.color = goodColor;
    //message.innerHTML = "Passwords Match!";
    //button.disabled = false;
    document.getElementById('submitpwd').removeAttribute("disabled");
 }else{
    pass2.style.backgroundColor = badColor;
    //message.style.color = badColor;
    //message.innerHTML = "Passwords Do Not Match!";
    //button.disabled = true;
    document.getElementById('submitpwd').setAttribute("disabled","disabled");
  }
}
