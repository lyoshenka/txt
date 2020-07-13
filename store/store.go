package store

import (
	"sync"
	"time"
)

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
		if !v.isExpired() {
			delete(s.data, k)
		}
	}
}
