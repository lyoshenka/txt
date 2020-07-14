#!/bin/bash

reflex --decoration=none --start-service=true --inverse-regex='bindata\.go' -- sh -c "go generate && go run ."
