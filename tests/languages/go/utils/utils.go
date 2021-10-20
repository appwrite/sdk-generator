package utils

import (
	"math/rand"
	"time"
)

<<<<<<< HEAD
=======
// CtxKey - a context Key
type CtxKey int

>>>>>>> e4c5048c (Adding Test and bug fixes in Client Template)
const (
	charset = "abcdefghijklmnopqrstuvwxyz" +
		"ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
)

var seededRand *rand.Rand = rand.New(
	rand.NewSource(time.Now().UnixNano()))

// StringWithCharset - returns a random string with a given length
func StringWithCharset(length int) string {
	b := make([]byte, length)
	for i := range b {
		b[i] = charset[seededRand.Intn(len(charset))]
	}
	return string(b)
}
