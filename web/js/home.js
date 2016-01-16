/* Start random number generator seeding ASAP */
sjcl.random.startCollectors();


txt.ready(function(){
  var form = document.getElementById('txtform'),
      textarea = form.querySelector('textarea');

  document.getElementById('encryptDiv').style.visibility = 'visible';

  function setInputsEnabled(isEnabled) {
    var inputs = form.querySelectorAll('input, textarea, select, button');
    Array.prototype.forEach.call(inputs, function(el) {
      el.disabled = !isEnabled;
    });
  };


  form.onsubmit = function(ev) {

    ev.preventDefault();

    var plaintext = textarea.value;

    if (!plaintext.trim()) {
      txt.shake(textarea);
      return;
    }


    var onSuccess = function(key) {
      setInputsEnabled(true)
      if (this.status >= 200 && this.status < 400) {
        var data = JSON.parse(this.responseText),
            flash = document.querySelector('.flash'),
            url = data.url + (key ? '#'+key : '');
        flash.querySelector('a').href = url;
        flash.querySelector('a').textContent = url;
        flash.style.display = 'block';
      }
      else {
        try {
          var errResp = JSON.parse(this.responseText);
          txt.alert(errResp.error);
        }
        catch (e) {
          txt.alert("Could not parse JSON.<br>" + e + "<br><br>" + this.responseText);
        }
      }
    };

    var onFail = function() {
      txt.alert('POST failed.');
    };


    setInputsEnabled(false);

    if (document.getElementById('encryptCheckbox').checked) {
      var key = txt.makeKey(256),
          ciphertext = txt.encrypt(key, plaintext);

      if (!ciphertext) {
        key = null;
        txt.alert('Txt could not be encrypted. Aborting.');
        return;
      }

      txt.ajaxPost(this, txt.curry(onSuccess, key), onFail, function(el) {
        var val = el.value == plaintext ? ciphertext : el.value;
        return encodeURIComponent(el.name) + '=' + encodeURIComponent(val);
      });
    }
    else {
      txt.ajaxPost(this, txt.curry(onSuccess, null), onFail);
    }
  };
});