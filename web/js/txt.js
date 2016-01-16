window.txt = {

  addClass: function(el, className) {
    if (el.classList) {
      el.classList.add(className);
    } else {
      el.className += ' ' + className;
    }
  },

  removeClass: function(el, className) {
    if (el.classList) {
      el.classList.remove(className);
    } else {
      el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
    }
  },

  ready: function(fn) {
    if (document.readyState != 'loading'){
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  },

  curry: function (fn) {
    var args = Array.prototype.slice.call(arguments, 1);
    return function () {
      return fn.apply(this, args.concat(Array.prototype.slice.call(arguments, 0)));
    };
  },

  ajaxPost: function(form, successCb, errorCb, mapCb) {
    var url = form.action,
        xhr = new XMLHttpRequest();

    var params = [].filter.call(form.elements, function(el) {
      // Allow only elements that don't have the 'checked' property, or those who have it and it's checked for them.
      // i.e. filter out checkboxes/radios which aren't checked.
      return typeof(el.checked) === 'undefined' || el.checked;
    })
    .filter(function(el) { return !!el.name; }) //Nameless elements die.
    .filter(function(el) { return el.disabled; }) //Disabled elements die.
    .map(mapCb || function(el) { // cal map function if provided
      // Map each field into a name=value string, make sure to properly escape!
      return encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value);
    }).join('&'); //Then join all the strings by &

    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.setRequestHeader("Accept", "application/json");

    if (successCb)
    {
      xhr.onload = successCb.bind(xhr);
    }
    if (errorCb) // for connection errors
    {
      xhr.onerror = errorCb.bind(xhr);
    }

    xhr.send(params);
  },

  /** Create a random base64-like string long enought to be suitable as an encryption key */
  makeKey: function (entropy) {
      entropy = Math.ceil(entropy / 6) * 6; /* non-6-multiple produces same-length base64 */
      var key = sjcl.bitArray.clamp(sjcl.random.randomWords(Math.ceil(entropy / 32), 0), entropy);
      return sjcl.codec.base64.fromBits(key, 0).replace(/\=+$/, '').replace(/\//, '-');
  },

  alert: (function (message, hideAfter) {
    var currentTimeout;
    return function(message, hideAfter) {
      var alert = document.getElementById('alert'),
          contTime = 1;
      if (currentTimeout) {
        clearTimeout(currentTimeout);
        alert.style.top = '-10000px';
        contTime = 100;
      }
      setTimeout(function(){
        alert.querySelector('span').innerHTML = message;
        alert.style.top = 0;
        if (hideAfter !== true) {
          currentTimeout = setTimeout(function() { alert.style.top = '-10000px'; currentTimeout = null; }, hideAfter === undefined ? 3000 : hideAfter);
        }
      }, contTime);
    }
  })(),

  /** Return an link object with the URL as href so you can extract host, protocol, hash, etc.
    This function use a closure to store a <div> parent for the <a> because IE requires the link
    be processed by it's HTML parser for the URL to be parsed. */
  parseUrl: (function () {
    var div = document.createElement('div');
    div.innerHTML = "<a></a>";
    return function (url) {
      div.firstChild.href = url;
      div.innerHTML = div.innerHTML;
      return div.firstChild;
    };
  })(),

  getDecryptKey: function (url) {
    var loc = url ? txt.parseUrl(url) : window.location;
    return loc.hash.replace('#', '').replace(/(\?|&).*$/, '');
  },

  utf8toBase64: function (text) {
    return sjcl.codec.base64.fromBits(sjcl.codec.utf8String.toBits(text));
  },

  base64toUtf8: function (text) {
    return sjcl.codec.utf8String.fromBits(sjcl.codec.base64.toBits(text));
  },

  encrypt: function (key, plaintext) {
    try {
      return txt.utf8toBase64(
        sjcl.encrypt(
          key,
          lzw.compress(
            txt.utf8toBase64(plaintext)
          )
        )
      );
    } catch (err) {
      return null;
    }
  },

  decrypt: function (key, ciphertext) {
    try {
      return txt.base64toUtf8(
        lzw.decompress(
          sjcl.decrypt(
            key,
            txt.base64toUtf8(ciphertext)
          )
        )
      );
    } catch (err) {
      return null;
    }
  },

  shake: function (el) {
    this.addClass(el, 'shake');
    setTimeout(this.curry(this.removeClass, el, 'shake'), 300);
  }
};

txt.addClass(document.body, ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch ? 'touch' :'no-touch');