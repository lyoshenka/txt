package main

import (
	"context"
	"errors"
	"fmt"
	"io"
	"math/rand"
	"net/http"
	"net/url"
	"os"
	"os/signal"
	"regexp"
	"strings"
	"sync"
	"syscall"
	"time"
)

const (
	keyLength   = 4
	maxDataSize = 1024 * 1024 * 10
)

var globalStore = NewStore()

func main() {
	http.DefaultClient.Timeout = 10 * time.Second

	port := os.Getenv("PORT")
	if port == "" {
		port = "8080"
	}

	h := http.NewServeMux()
	h.Handle("/", http.HandlerFunc(handler))

	srv := &http.Server{Addr: ":" + port, Handler: h}

	go func() {
		interruptChan := make(chan os.Signal, 1)
		signal.Notify(interruptChan, os.Interrupt, syscall.SIGTERM)
		<-interruptChan
		err := srv.Shutdown(context.Background())
		checkErr(err)
	}()

	go func() {
		for {
			<-time.Tick(1 * time.Hour)
			logIt("cleaning global store")
			globalStore.Clean()
		}
	}()

	logIt("Listening on %s", srv.Addr)
	err := srv.ListenAndServe()
	if !errors.Is(err, http.ErrServerClosed) {
		panic(err)
	}
}

func handler(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	logIt("%s %s", strings.ToUpper(r.Method), r.URL.Path)

	if r.URL.Path == "/" {
		doRoot(w, r)
		return
	}

	if r.Method != http.MethodGet {
		send404(w)
		return
	}

	key := strings.TrimLeft(r.URL.Path, "/")

	if len(key) != keyLength {
		send404(w)
		return
	}

	e := globalStore.Get(key)
	if e == nil {
		send404(w)
		return
	}

	w.Write(e)
}

func doRoot(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	default:
		send404(w)
	case http.MethodGet:
		if regexp.MustCompile("(curl|wget)").MatchString(strings.ToLower(r.UserAgent())) {
			w.Write([]byte("Welcome to txt. See https://github.com/lyoshenka/txt for more info.\n"))
			return
		}
		w.Write(indexHTML)
	case http.MethodPost:
		var value []byte

		txtHeader := r.Header.Get("X-Txt")
		if txtHeader != "" {
			unescaped, err := url.PathUnescape(txtHeader)
			if err != nil {
				value = []byte(txtHeader)
			} else {
				value = []byte(unescaped)
			}
		} else {
			data := make([]byte, maxDataSize+2)
			n, err := r.Body.Read(data)
			if err != nil && err != io.EOF {
				w.WriteHeader(500)
				w.Write([]byte(err.Error()))
				return
			}
			if n > maxDataSize {
				w.WriteHeader(400)
				w.Write([]byte(fmt.Sprintf("Max data size is %d", maxDataSize)))
				return
			}

			value = data[:n]
		}

		key := newKey(keyLength)
		globalStore.Set(key, value, time.Now().Add(24*time.Hour))

		proto := r.Header.Get("x-forwarded-proto")
		if proto == "" {
			proto = "http"
		}

		w.Write([]byte(fmt.Sprintf("%s://%s/%s\n", proto, r.Host, key)))
	}
}

var letterRunes = []rune("abcdefghjkmnoprstuvwxyz")

func newKey(n int) string {
	b := make([]rune, n)
	for i := range b {
		b[i] = letterRunes[rand.Intn(len(letterRunes))]
	}
	return string(b)
}

func logIt(format string, a ...interface{}) (n int, err error) {
	//fmt.Fprintf(os.Stderr, "[%s] ", time.Now().Format(time.RFC3339))
	//return fmt.Fprintf(os.Stderr, format+"\n", a...)
	return fmt.Printf(format+"\n", a...)
}

func send404(w http.ResponseWriter) {
	w.WriteHeader(http.StatusNotFound)
	w.Write([]byte("404 not found"))
}

func checkErr(e error) {
	if e != nil {
		panic(e)
	}
}


type entry struct {
	data    []byte
	expires time.Time
}

func (e entry) isExpired() bool {
	return !e.expires.IsZero() && time.Now().After(e.expires)
}

type store struct {
	data map[string]entry
	mu   *sync.RWMutex
}

func NewStore() *store {
	return &store{
		data: make(map[string]entry),
		mu:   &sync.RWMutex{},
	}
}

func (s *store) Get(key string) []byte {
	s.mu.RLock()
	defer s.mu.RUnlock()

	if e, ok := s.data[key]; ok && !e.isExpired() {
		return e.data
	}

	return nil
}

func (s *store) Set(key string, content []byte, expires time.Time) {
	s.mu.Lock()
	defer s.mu.Unlock()
	s.data[key] = entry{
		data:    content,
		expires: expires,
	}
}

func (s *store) Clean() {
	s.mu.Lock()
	defer s.mu.Unlock()

	for k, v := range s.data {
		if v.isExpired() {
			delete(s.data, k)
		}
	}
}


var indexHTML = []byte(`<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TXT - a simple pastebin</title>

    <style>
      /**
       * Minified by jsDelivr using clean-css v4.2.1.
       * Original file: /npm/@exampledev/new.css@1.1.2/new.css
       */
      :root{--nc-font-sans:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";--nc-font-mono:Consolas,monaco,'Ubuntu Mono','Liberation Mono','Courier New',Courier,monospace;--nc-tx-1:#000000;--nc-tx-2:#1A1A1A;--nc-bg-1:#FFFFFF;--nc-bg-2:#F6F8FA;--nc-bg-3:#E5E7EB;--nc-lk-1:#0070F3;--nc-lk-2:#0366D6;--nc-lk-tx:#FFFFFF;--nc-ac-1:#79FFE1;--nc-ac-tx:#0C4047}@media (prefers-color-scheme:dark){:root{--nc-tx-1:#ffffff;--nc-tx-2:#eeeeee;--nc-bg-1:#000000;--nc-bg-2:#111111;--nc-bg-3:#222222;--nc-lk-1:#3291FF;--nc-lk-2:#0070F3;--nc-lk-tx:#FFFFFF;--nc-ac-1:#7928CA;--nc-ac-tx:#FFFFFF}}*{margin:0;padding:0}address,area,article,aside,audio,blockquote,datalist,details,dl,fieldset,figure,form,iframe,img,input,meter,nav,ol,optgroup,option,output,p,pre,progress,ruby,section,table,textarea,ul,video{margin-bottom:1rem}button,html,input,select{font-family:var(--nc-font-sans)}body{margin:0 auto;max-width:750px;padding:2rem;border-radius:6px;overflow-x:hidden;word-break:break-word;overflow-wrap:break-word;background:var(--nc-bg-1);color:var(--nc-tx-2);font-size:1.03rem;line-height:1.5}::selection{background:var(--nc-ac-1);color:var(--nc-ac-tx)}h1,h2,h3,h4,h5,h6{line-height:1;color:var(--nc-tx-1);padding-top:.875rem}h1,h2,h3{color:var(--nc-tx-1);padding-bottom:2px;margin-bottom:8px;border-bottom:1px solid var(--nc-bg-2)}h4,h5,h6{margin-bottom:.3rem}h1{font-size:2.25rem}h2{font-size:1.85rem}h3{font-size:1.55rem}h4{font-size:1.25rem}h5{font-size:1rem}h6{font-size:.875rem}a{color:var(--nc-lk-1)}a:hover{color:var(--nc-lk-2)}abbr:hover{cursor:help}blockquote{padding:1.5rem;background:var(--nc-bg-2);border-left:5px solid var(--nc-bg-3)}abbr{cursor:help}blockquote :last-child{padding-bottom:0;margin-bottom:0}header{background:var(--nc-bg-2);border-bottom:1px solid var(--nc-bg-3);padding:2rem 1.5rem;margin:-2rem calc(0px - (50vw - 50%)) 2rem;padding-left:calc(50vw - 50%);padding-right:calc(50vw - 50%)}header h1,header h2,header h3{padding-bottom:0;border-bottom:0}header>:first-child{margin-top:0;padding-top:0}header>:last-child{margin-bottom:0}a button,button,input[type=button],input[type=reset],input[type=submit]{font-size:1rem;display:inline-block;padding:6px 12px;text-align:center;text-decoration:none;white-space:nowrap;background:var(--nc-lk-1);color:var(--nc-lk-tx);border:0;border-radius:4px;box-sizing:border-box;cursor:pointer;color:var(--nc-lk-tx)}a button[disabled],button[disabled],input[type=button][disabled],input[type=reset][disabled],input[type=submit][disabled]{cursor:default;opacity:.5;cursor:not-allowed}.button:focus,.button:hover,button:focus,button:hover,input[type=button]:focus,input[type=button]:hover,input[type=reset]:focus,input[type=reset]:hover,input[type=submit]:focus,input[type=submit]:hover{background:var(--nc-lk-2)}code,kbd,pre,samp{font-family:var(--nc-font-mono)}code,kbd,pre,samp{background:var(--nc-bg-2);border:1px solid var(--nc-bg-3);border-radius:4px;padding:3px 6px;font-size:.9rem}kbd{border-bottom:3px solid var(--nc-bg-3)}pre{padding:1rem 1.4rem;max-width:100%;overflow:auto}pre code{background:inherit;font-size:inherit;color:inherit;border:0;padding:0;margin:0}code pre{display:inline;background:inherit;font-size:inherit;color:inherit;border:0;padding:0;margin:0}details{padding:.6rem 1rem;background:var(--nc-bg-2);border:1px solid var(--nc-bg-3);border-radius:4px}summary{cursor:pointer;font-weight:700}details[open]{padding-bottom:.75rem}details[open] summary{margin-bottom:6px}details[open]>:last-child{margin-bottom:0}dt{font-weight:700}dd::before{content:'→ '}hr{border:0;border-bottom:1px solid var(--nc-bg-3);margin:1rem auto}fieldset{margin-top:1rem;padding:2rem;border:1px solid var(--nc-bg-3);border-radius:4px}legend{padding:auto .5rem}table{border-collapse:collapse;width:100%}td,th{border:1px solid var(--nc-bg-3);text-align:left;padding:.5rem}th{background:var(--nc-bg-2)}tr:nth-child(even){background:var(--nc-bg-2)}table caption{font-weight:700;margin-bottom:.5rem}textarea{max-width:100%}ol,ul{padding-left:2rem}li{margin-top:.4rem}ol ol,ol ul,ul ol,ul ul{margin-bottom:0}mark{padding:3px 6px;background:var(--nc-ac-1);color:var(--nc-ac-tx)}input,select,textarea{padding:6px 12px;margin-bottom:.5rem;background:var(--nc-bg-2);color:var(--nc-tx-2);border:1px solid var(--nc-bg-3);border-radius:4px;box-shadow:none;box-sizing:border-box}img{max-width:100%}


      html {
        font-size: 18px;
      }
      textarea {
        width: 100%;
        font-size: 1rem;
      }
      button {
        font-size: 1.1rem;
        padding: 10px 15px;
        margin-right: 1rem;
      }
      section:not:last-of-type {
        margin-bottom: 3rem;
      }
      flash {
        margin: 20px 0;
        border: solid 1px black;
        padding: 10px;
      }
      flash span {
        float: right;
        cursor: pointer;
      }
    </style>
  </head>

  <body>
    <div class="center">
      <header><h1>TXT - A Simple Pastebin</h1></header>
      <section>
        <flash style="display: none">
          Txt created at <a href=""></a>
          <span class="close" onclick="this.parentNode.style.display='none'">&#10006;</span>
        </flash>
        <form method="POST" action="/">
          <div>
            <textarea name="data" rows=10 autofocus placeholder="Text goes here"></textarea>
          </div>
          <button type="submit">👉  Make a TXT  👈</button>
          It will expire in 24 hours.
        </form>
      </section>


      <section>
        <h2>CLI</h2>
        <pre>echo "your text here" | curl -d @- https://txt.grin.io</pre>
      </section>

      <section>
        <h2>About</h2>
        <p>
        <a href="https://github.com/lyoshenka/txt">Source &amp; Docs</a>
        </p>
      </section>
    </div>

    <script>

      ready = function (fn) {
        if (document.readyState != 'loading') { fn(); }
        else { document.addEventListener('DOMContentLoaded', fn); }
      };

      ajaxPost = function (url, data, successCb, errorCb) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.onload = successCb.bind(xhr);
        xhr.onerror = errorCb.bind(xhr);
        xhr.send(data);
      };

      ready(function () {
        const form = document.querySelector('form');
        const textarea = form.querySelector('textarea');
        const flash = document.querySelector('flash');

        function setInputsEnabled(isEnabled) {
          const inputs = form.querySelectorAll('input, textarea, select, button');
          Array.prototype.forEach.call(inputs, function (el) {
            el.disabled = !isEnabled;
          });
        };


        form.onsubmit = function (ev) {
          ev.preventDefault();
          if (!textarea.value.trim())  { return; }

          const onSuccess = function () {
            setInputsEnabled(true)
            if (this.status >= 200 && this.status < 400)
            {
              const url = this.responseText;
              flash.querySelector('a').href = url;
              flash.querySelector('a').textContent = url;
              flash.style.display = 'block';
            }
            else
            {
              alert("ERROR: " + this.responseText);
            }
          };

          const onFail = function () { alert('POST failed.'); };

          setInputsEnabled(false);

          ajaxPost(form.action, textarea.value, onSuccess, onFail);
        };

      });
    </script>
  </body>
</html>`)
