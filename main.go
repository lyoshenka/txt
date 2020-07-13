package main

import (
	"context"
	"errors"
	"fmt"
	"io"
	"math/rand"
	"net/http"
	"os"
	"os/signal"
	"regexp"
	"strconv"
	"strings"
	"syscall"
	"time"

	"github.com/lyoshenka.txt/store"
)

const (
	keyLength   = 6
	maxDataSize = 1024 * 1024 * 10
)

var (
	globalStore = store.NewStore()
	domain      = "localhost"
	port        = "8080"
	proto       = "http"
)

func main() {
	rand.Seed(time.Now().UnixNano())
	http.DefaultClient.Timeout = 10 * time.Second

	p := os.Getenv("PORT")
	if p != "" {
		port = p
	}

	d := os.Getenv("DOMAIN")
	if d != "" {
		domain = d
	}

	if os.Getenv("SSL") != "" {
		proto = "https"
	}

	if proto == "http" && port != "80" || proto == "https" && port != "443" {
		domain = domain + ":" + port
	}

	h := http.NewServeMux()
	h.Handle("/", http.HandlerFunc(handler))

	srv := &http.Server{
		Addr:    ":" + port,
		Handler: h,
	}

	go func() {
		interruptChan := make(chan os.Signal, 1)
		signal.Notify(interruptChan, os.Interrupt, syscall.SIGTERM)
		<-interruptChan
		err := srv.Shutdown(context.Background())
		checkErr(err)
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

	if !regexp.MustCompile("[a-zA-Z0-9]{" + strconv.Itoa(keyLength) + "}").MatchString(key) {
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
		w.Write([]byte("Welcome to txt. See https://github.com/lyoshenka/txt for more info.\n"))
	case http.MethodPost:
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

		key := newKey(keyLength)

		globalStore.Set(key, data[:n], time.Now().Add(20*time.Second))
		w.Write([]byte(fmt.Sprintf("%s://%s/%s", proto, domain, key)))
	}
}

var letterRunes = []rune("abcdefghijkmnopqrstuvwxyz")

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
