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
  var goodColor = "#66cc66";
  var badColor = "#ff6666";
  if((pass1.value != '') && (pass1.value == pass2.value)){
    pass2.style.backgroundColor = goodColor;
    document.getElementById('submitpwd').removeAttribute("disabled");
  }else{
    pass2.style.backgroundColor = badColor;
    document.getElementById('submitpwd').setAttribute("disabled","disabled");
  }
}
function checkFrequency(){
  // Set the colours
  var goodColor = "#66cc66";
  var badColor = "#ff6666";
  // Get the objects from the config page
  var freqTRX = document.getElementById('confFREQ');
  var freqRX = document.getElementById('confFREQrx');
  var freqTX = document.getElementById('confFREQtx');
  var freqPOCSAG = document.getElementById('pocsagFrequency');
  if(freqTRX){
    var intFreqTRX = parseFloat(freqTRX.value); // Swap to float
    // TRX Good
    if (144 <= intFreqTRX && intFreqTRX <= 148)   { confFREQ.style.backgroundColor = goodColor; }
    if (219 <= intFreqTRX && intFreqTRX <= 225)   { confFREQ.style.backgroundColor = goodColor; }
    if (420 <= intFreqTRX && intFreqTRX <= 475)   { confFREQ.style.backgroundColor = goodColor; }
    if (842 <= intFreqTRX && intFreqTRX <= 950)   { confFREQ.style.backgroundColor = goodColor; }
    // TRX Bad
    if (145.8 <= intFreqTRX && intFreqTRX <= 146) { confFREQ.style.backgroundColor = badColor; }
    if (435 <= intFreqTRX && intFreqTRX <= 438)   { confFREQ.style.backgroundColor = badColor; }
  }
  if(freqRX){
    var intFreqRX = parseFloat(freqRX.value); // Swap to float
    // RX Good
    if (144 <= intFreqRX && intFreqRX <= 148)   { confFREQrx.style.backgroundColor = goodColor; }
    if (219 <= intFreqRX && intFreqRX <= 225)   { confFREQrx.style.backgroundColor = goodColor; }
    if (420 <= intFreqRX && intFreqRX <= 475)   { confFREQrx.style.backgroundColor = goodColor; }
    if (842 <= intFreqRX && intFreqRX <= 950)   { confFREQrx.style.backgroundColor = goodColor; }
    // RX Bad
    if (145.8 <= intFreqRX && intFreqRX <= 146) { confFREQrx.style.backgroundColor = badColor; }
    if (435 <= intFreqRX && intFreqRX <= 438)   { confFREQrx.style.backgroundColor = badColor; }
  }
  if(freqTX){
    var intFreqTX = parseFloat(freqTX.value); // Swap to float
    // TX Good
    if (144 <= intFreqTX && intFreqTX <= 148)   { confFREQtx.style.backgroundColor = goodColor; }
    if (219 <= intFreqTX && intFreqTX <= 225)   { confFREQtx.style.backgroundColor = goodColor; }
    if (420 <= intFreqTX && intFreqTX <= 475)   { confFREQtx.style.backgroundColor = goodColor; }
    if (842 <= intFreqTX && intFreqTX <= 950)   { confFREQtx.style.backgroundColor = goodColor; }
    // TX Bad
    if (145.8 <= intFreqTX && intFreqTX <= 146) { confFREQtx.style.backgroundColor = badColor; }
    if (435 <= intFreqTX && intFreqTX <= 438)   { confFREQtx.style.backgroundColor = badColor; }
  }
  if(freqPOCSAG){
    var intFreqPOCSAG = parseFloat(freqPOCSAG.value); // Swap to float
    // TX Good
    if (144 <= intFreqPOCSAG && intFreqPOCSAG <= 148)   { pocsagFrequency.style.backgroundColor = goodColor; }
    if (219 <= intFreqPOCSAG && intFreqPOCSAG <= 225)   { pocsagFrequency.style.backgroundColor = goodColor; }
    if (420 <= intFreqPOCSAG && intFreqPOCSAG <= 475)   { pocsagFrequency.style.backgroundColor = goodColor; }
    if (842 <= intFreqPOCSAG && intFreqPOCSAG <= 950)   { pocsagFrequency.style.backgroundColor = goodColor; }
    // TX Bad
    if (145.8 <= intFreqPOCSAG && intFreqPOCSAG <= 146) { pocsagFrequency.style.backgroundColor = badColor; }
    if (435 <= intFreqPOCSAG && intFreqPOCSAG <= 438)   { pocsagFrequency.style.backgroundColor = badColor; }
  }
}
