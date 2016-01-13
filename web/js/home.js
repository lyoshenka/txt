function ready(fn) {
  if (document.readyState != 'loading'){
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn);
  }
}

function ajaxPost (form, successCb, errorCb) {
  var url = form.action,
      xhr = new XMLHttpRequest();

  //This is a bit tricky, [].fn.call(form.elements, ...) allows us to call .fn
  //on the form's elements, even though it's not an array. Effectively
  //Filtering all of the fields on the form
  var params = [].filter.call(form.elements, function(el) {
    //Allow only elements that don't have the 'checked' property
    //Or those who have it, and it's checked for them.
    return typeof(el.checked) === 'undefined' || el.checked;
    //Practically, filter out checkboxes/radios which aren't checked.
  })
  .filter(function(el) { return !!el.name; }) //Nameless elements die.
  .filter(function(el) { return el.disabled; }) //Disabled elements die.
  .map(function(el) {
      //Map each field into a name=value string, make sure to properly escape!
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

  //All preperations are clear, send the request!
  xhr.send(params);
}

ready(function(){
  var form = document.getElementById('txtform');
  form.onsubmit = function() {
    form.querySelector('button[type="submit"]').disabled = true;
    ajaxPost(this, function() {
      form.querySelector('button[type="submit"]').disabled = false;
      if (this.status >= 200 && this.status < 400) {
        var data = JSON.parse(this.responseText);
        var flash = document.querySelector('.flash');
        flash.querySelector('a').href = data.url;
        flash.querySelector('a').textContent = data.url;
        flash.style.display = 'block';
        form.querySelector('textarea').value = '';
      }
      else {
        alert(this.responseText);
      }
    });
    return false;
  };
});