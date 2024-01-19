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
function checkPsk() {
	if(psk1.value.length > 0 && psk1.value.length < 8) {
		psk1.style.background='#ff6666';
	} else {
		psk1.style.background='#66cc66';
	}
}
function checkPskMatch(){                   //used in confirm matching psk entries
  var psk1 = document.getElementById('psk1');
  var psk2 = document.getElementById('psk2');
  var goodColor = "#66cc66";
  var badColor = "#ff6666";
  if((psk1.value != '') && (psk1.value == psk2.value)){
    psk2.style.backgroundColor = goodColor;
    document.getElementById('submitpsk').removeAttribute("disabled");
  }else{
    psk2.style.backgroundColor = badColor;
    document.getElementById('submitpsk').setAttribute("disabled","disabled");
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
    confFREQ.style.backgroundColor = badColor;		// Set to bad colour first, then check
    var intFreqTRX = parseFloat(freqTRX.value);		// Swap to float
    // TRX Good
    if (144 <= intFreqTRX && intFreqTRX <= 148)   { confFREQ.style.backgroundColor = goodColor; }
    if (220 <= intFreqTRX && intFreqTRX <= 225)   { confFREQ.style.backgroundColor = goodColor; }
    if (420 <= intFreqTRX && intFreqTRX <= 450)   { confFREQ.style.backgroundColor = goodColor; }
    if (842 <= intFreqTRX && intFreqTRX <= 950)   { confFREQ.style.backgroundColor = goodColor; }
    if (1240 <= intFreqTRX && intFreqTRX <= 1300) { confFREQ.style.backgroundColor = goodColor; }
    // TRX Bad
    if (145.8 <= intFreqTRX && intFreqTRX <= 146) { confFREQ.style.backgroundColor = badColor; }
    if (435 <= intFreqTRX && intFreqTRX <= 438)   { confFREQ.style.backgroundColor = badColor; }
    if (1260 <= intFreqTRX && intFreqTRX <= 1270) { confFREQ.style.backgroundColor = badColor; }
  }
  if(freqRX){
    confFREQrx.style.backgroundColor = badColor;	// Set to bad colour first, then check
    var intFreqRX = parseFloat(freqRX.value);		// Swap to float
    // RX Good
    if (144 <= intFreqRX && intFreqRX <= 148)   { confFREQrx.style.backgroundColor = goodColor; }
    if (220 <= intFreqRX && intFreqRX <= 225)   { confFREQrx.style.backgroundColor = goodColor; }
    if (420 <= intFreqRX && intFreqRX <= 450)   { confFREQrx.style.backgroundColor = goodColor; }
    if (842 <= intFreqRX && intFreqRX <= 950)   { confFREQrx.style.backgroundColor = goodColor; }
    if (1240 <= intFreqRX && intFreqRX <= 1300) { confFREQrx.style.backgroundColor = goodColor; }
    // RX Bad
    if (145.8 <= intFreqRX && intFreqRX <= 146) { confFREQrx.style.backgroundColor = badColor; }
    if (435 <= intFreqRX && intFreqRX <= 438)   { confFREQrx.style.backgroundColor = badColor; }
    if (1260 <= intFreqRX && intFreqRX <= 1270) { confFREQrx.style.backgroundColor = badColor; }
  }
  if(freqTX){
    confFREQtx.style.backgroundColor = badColor;	// Set to bad colour first, then check
    var intFreqTX = parseFloat(freqTX.value);		// Swap to float
    // TX Good
    if (144 <= intFreqTX && intFreqTX <= 148)   { confFREQtx.style.backgroundColor = goodColor; }
    if (220 <= intFreqTX && intFreqTX <= 225)   { confFREQtx.style.backgroundColor = goodColor; }
    if (420 <= intFreqTX && intFreqTX <= 450)   { confFREQtx.style.backgroundColor = goodColor; }
    if (842 <= intFreqTX && intFreqTX <= 950)   { confFREQtx.style.backgroundColor = goodColor; }
    if (1240 <= intFreqTX && intFreqTX <= 1300) { confFREQtx.style.backgroundColor = goodColor; }
    // TX Bad
    if (145.8 <= intFreqTX && intFreqTX <= 146) { confFREQtx.style.backgroundColor = badColor; }
    if (435 <= intFreqTX && intFreqTX <= 438)   { confFREQtx.style.backgroundColor = badColor; }
    if (1260 <= intFreqTX && intFreqTX <= 1270) { confFREQtx.style.backgroundColor = badColor; }
  }
  if(freqPOCSAG){
    pocsagFrequency.style.backgroundColor = badColor;		// Set to bad colour first, then check
    var intFreqPOCSAG = parseFloat(freqPOCSAG.value);		// Swap to float
    // TX Good
    if (144 <= intFreqPOCSAG && intFreqPOCSAG <= 148)   { pocsagFrequency.style.backgroundColor = goodColor; }
    if (220 <= intFreqPOCSAG && intFreqPOCSAG <= 225)   { pocsagFrequency.style.backgroundColor = goodColor; }
    if (420 <= intFreqPOCSAG && intFreqPOCSAG <= 450)   { pocsagFrequency.style.backgroundColor = goodColor; }
    if (842 <= intFreqPOCSAG && intFreqPOCSAG <= 950)   { pocsagFrequency.style.backgroundColor = goodColor; }
    // TX Bad
    if (145.8 <= intFreqPOCSAG && intFreqPOCSAG <= 146) { pocsagFrequency.style.backgroundColor = badColor; }
    if (435 <= intFreqPOCSAG && intFreqPOCSAG <= 438)   { pocsagFrequency.style.backgroundColor = badColor; }
  }
}
function toggleDMRCheckbox(event) {
  switch(document.getElementById('aria-toggle-dmr').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmr').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-dmr').click();
      break;
    case "false":
      document.getElementById('aria-toggle-dmr').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-dmr').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmr').click(); }
}
function toggleDSTARCheckbox(event) {
  switch(document.getElementById('aria-toggle-dstar').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dstar').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-dstar').click();
      break;
    case "false":
      document.getElementById('aria-toggle-dstar').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-dstar').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dstar').click(); }
}
function toggleYSFCheckbox(event) {
  switch(document.getElementById('aria-toggle-ysf').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-ysf').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-ysf').click();
      break;
    case "false":
      document.getElementById('aria-toggle-ysf').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-ysf').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-ysf').click(); }
}
function toggleP25Checkbox(event) {
  switch(document.getElementById('aria-toggle-p25').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-p25').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-p25').click();
      break;
    case "false":
      document.getElementById('aria-toggle-p25').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-p25').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-p25').click(); }
}
function toggleNXDNCheckbox(event) {
  switch(document.getElementById('aria-toggle-nxdn').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-nxdn').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-nxdn').click();
      break;
    case "false":
      document.getElementById('aria-toggle-nxdn').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-nxdn').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-nxdn').click(); }
}
function toggleM17Checkbox(event) {
  switch(document.getElementById('aria-toggle-m17').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-m17').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-m17').click();
      break;
    case "false":
      document.getElementById('aria-toggle-m17').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-m17').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-m17').click(); }
}
function toggleYSF2DMRCheckbox(event) {
  switch(document.getElementById('aria-toggle-ysf2dmr').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-ysf2dmr').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-ysf2dmr').click();
      break;
    case "false":
      document.getElementById('aria-toggle-ysf2dmr').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-ysf2dmr').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-ysf2dmr').click(); }
}
function toggleYSF2NXDNCheckbox(event) {
  switch(document.getElementById('aria-toggle-ysf2nxdn').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-ysf2nxdn').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-ysf2nxdn').click();
      break;
    case "false":
      document.getElementById('aria-toggle-ysf2nxdn').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-ysf2nxdn').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-ysf2nxdn').click(); }
}
function toggleYSF2P25Checkbox(event) {
  switch(document.getElementById('aria-toggle-ysf2p25').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-ysf2p25').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-ysf2p25').click();
      break;
    case "false":
      document.getElementById('aria-toggle-ysf2p25').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-ysf2p25').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-ysf2p25').click(); }
}
function toggleDMR2YSFCheckbox(event) {
  switch(document.getElementById('aria-toggle-dmr2ysf').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmr2ysf').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-dmr2ysf').click();
      break;
    case "false":
      document.getElementById('aria-toggle-dmr2ysf').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-dmr2ysf').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmr2ysf').click(); }
}
function toggleDMR2NXDNCheckbox(event) {
  switch(document.getElementById('aria-toggle-dmr2nxdn').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmr2nxdn').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-dmr2nxdn').click();
      break;
    case "false":
      document.getElementById('aria-toggle-dmr2nxdn').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-dmr2nxdn').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmr2nxdn').click(); }
}
function togglePOCSAGCheckbox(event) {
  switch(document.getElementById('aria-toggle-pocsag').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-pocsag').setAttribute('aria-checked', "false");
      //document.getElementById('toggle-pocsag').click();
      break;
    case "false":
      document.getElementById('aria-toggle-pocsag').setAttribute('aria-checked', "true");
      //document.getElementById('toggle-pocsag').click();
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-pocsag').click(); }
}
function toggleDmrGatewayNet1EnCheckbox(event) {
  switch(document.getElementById('aria-toggle-dmrGatewayNet1En').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmrGatewayNet1En').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-dmrGatewayNet1En').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmrGatewayNet1En').click(); }
}
function toggleDmrGatewayNet2EnCheckbox(event) {
  switch(document.getElementById('aria-toggle-dmrGatewayNet2En').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmrGatewayNet2En').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-dmrGatewayNet2En').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmrGatewayNet2En').click(); }
}
function toggleDmrGatewayXlxEnCheckbox(event) {
  switch(document.getElementById('aria-toggle-dmrGatewayXlxEn').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmrGatewayXlxEn').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-dmrGatewayXlxEn').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmrGatewayXlxEn').click(); }
}
function toggleDmrEmbeddedLCOnly(event) {
  switch(document.getElementById('aria-toggle-dmrEmbeddedLCOnly').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmrEmbeddedLCOnly').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-dmrEmbeddedLCOnly').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmrEmbeddedLCOnly').click(); }
}
function toggleDmrDumpTAData(event) {
  switch(document.getElementById('aria-toggle-dmrDumpTAData').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dmrDumpTAData').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-dmrDumpTAData').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dmrDumpTAData').click(); }
}
function toggleHostFilesYSFUpper(event) {
  switch(document.getElementById('aria-toggle-confHostFilesYSFUpper').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-confHostFilesYSFUpper').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-confHostFilesYSFUpper').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-confHostFilesYSFUpper').click(); }
}
function toggleWiresXCommandPassthrough(event) {
  switch(document.getElementById('aria-toggle-confWiresXCommandPassthrough').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-confWiresXCommandPassthrough').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-confWiresXCommandPassthrough').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-confWiresXCommandPassthrough').click(); }
}
function toggleDstarTimeAnnounce(event) {
  switch(document.getElementById('aria-toggle-timeAnnounce').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-timeAnnounce').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-timeAnnounce').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-timeAnnounce').click(); }
}
function toggleDstarDplusHostfiles(event) {
  switch(document.getElementById('aria-toggle-dplusHostFiles').getAttribute('aria-checked')) {
    case "true":
      document.getElementById('aria-toggle-dplusHostFiles').setAttribute('aria-checked', "false");
      break;
    case "false":
      document.getElementById('aria-toggle-dplusHostFiles').setAttribute('aria-checked', "true");
      break;
  }
  if(event.keyCode == '32') { document.getElementById('aria-toggle-dplusHostFiles').click(); }
}
