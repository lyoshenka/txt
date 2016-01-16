txt.ready(function() {
  var contentEl = document.getElementById('datum'),
      ciphertext = contentEl.textContent.trim(),
      key = txt.getDecryptKey();

  if (!ciphertext || !key) {
    return;
  }

  var plaintext = txt.decrypt(key, ciphertext);
  if (plaintext) {
    contentEl.textContent = plaintext;
    ciphertext = '';
  }
  else {
    txt.message('error', 'Could not decrypt data (Wrong key?)', 'Error');
  }
});