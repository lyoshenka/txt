<?php $url = 'http' . ($ssl ? 's' : ''). '://' . $domain ?>
<h2>New txt</h2>
<form method="POST" action="/">
  <div>
    <textarea style="width:100%" name="data" rows=6 autofocus placeholder="Put your txt here"></textarea><br>
  </div>

  <div>
    Expires in
    <select name="ttl">
      <option value="3600">1 hour</option>
      <option value="86400" selected>1 day</option>
      <option value="604800">1 week</option>
      <option value="2592000">1 month</option>
    </select>
  </div>

  <div>
    <input type="checkbox" name="once" />Delete after opening?
  </div>

  <button type="submit">Submit</button>
</form>

<h2>CLI</h2>
<pre>cat FILENAME | curl -F 'data=<-' <?php echo $url ?></pre>


<h2>API</h2>

<h3>Get</h3>
<pre>GET <?php echo $url ?>/c0d3?raw</pre>

<h3>Create</h3>
<pre>
POST <?php echo $url ?>?ttl=3600&once
data=<?php echo urlencode('s3cr3t passw0rd') ?>
</pre>

<h3>Optional GET Params</h3>
<dl>
  <dt>raw</dt>
  <dd>Get raw txt.</dd>

  <dt>json</dt>
  <dd>Respond with JSON (default is plain text).</dd>

  <dt>once</dt>
  <dd>Burn after reading. txt will be erased after the first time it is accessed. Great for sharing passwords.</dd>

  <dt>ttl=3600</dt>
  <dd>txt will be erased after this many seconds.</dd>

  <dt>auth_token=TOKEN</dt>
  <dd>txt can be configured to require authentication to create txts.</dd>
</dl>