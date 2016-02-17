<section>
  <h2>New Txt</h2>
  <div class='flash' style="display: none">
    Txt created at <a href=""></a>
    <span class="close" onclick="this.parentNode.style.display='none'">&#10006;</span>
  </div>
  <form method="POST" action="/" id="txtform">
    <div>
      <textarea name="data" rows=6 autofocus placeholder="Put your txt here"></textarea><br>
    </div>

    <div>
      Expires in
      <select name="ttl">
        <option value="3600">1 hour</option>
        <option value="86400">1 day</option>
        <option value="604800" selected>1 week</option>
        <option value="2592000">1 month</option>
      </select>
    </div>

    <div>
      <label><input type="checkbox" name="once"> <span>Delete after opening</span></label>
    </div>

    <div id="encryptDiv" style="visibility: hidden">
      <label><input type="checkbox" id="encryptCheckbox"> <span>Encrypt before submitting</span></label>
    </div>

    <button type="submit">Create</button>
  </form>
</section>


<section>
  <h2>CLI</h2>
  <pre>curl -F 'data=&lt;-' <?php echo $domain ?> &lt; FILENAME</pre>
</section>


<section>
  <h2>API</h2>

  <h3>Create</h3>
  <div class="indent">
    <pre>POST <?php echo $domain ?>?ttl=3600&amp;once=1<?php echo "\n" ?>data=<?php echo urlencode('s3cr3t passw0rd') ?></pre>

    <h4>Optional Params</h4>
    <dl>
      <dt>json=1</dt>
      <dd>Respond with JSON (default is plain text).</dd>

      <dt>once=1</dt>
      <dd>Burn after reading. txt will be erased after the first time it is accessed. Great for sharing passwords.</dd>

      <dt>ttl=SECONDS</dt>
      <dd>txt will be erased after this many seconds.</dd>

      <dt>auth=TOKEN</dt>
      <dd>txt can be configured to require authentication to create txts.</dd>
    </dl>
  </div>

  <h3>Get</h3>
  <div class="indent">
    <pre>GET <?php echo $domain ?>/c0d3?raw</pre>

    <h4>Optional Params</h4>
    <dl>
      <dt>?raw</dt>
      <dd>Get raw txt.</dd>

      <dt>?json</dt>
      <dd>Get JSON.</dd>
    </dl>
  </div>
</section>

<section>
  <h2>About</h2>
  <a href="https://github.com/lyoshenka/txt">Source &amp; Docs</a>
</section>
<script src="/js/lzw.js"></script>
<script src="/js/sjcl.js"></script>
<script src="/js/txt.js"></script>
<script src="/js/home.js"></script>
