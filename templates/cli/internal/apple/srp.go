package apple

import (
	"crypto/pbkdf2"
	"crypto/sha1"
	"crypto/sha256"
	"errors"
	"fmt"
	"io"
	"math/big"
	"strconv"
	"strings"
	"time"
)

// Apple signs in with SRP-6a over the 2048-bit group of RFC 5054 and SHA-256,
// so the password itself never leaves the machine. This follows
// fastlane-sirp, the SRP client spaceship uses, byte for byte. Its quirks
// matter: x leaves the username out, and A, S and the server proof are
// hashed and sent without leading zero bytes, while k and u pad their inputs
// to the length of N.

var (
	srpN = mustHex(strings.Join([]string{
		"AC6BDB41324A9A9BF166DE5E1389582FAF72B6651987EE07FC3192943DB56050",
		"A37329CBB4A099ED8193E0757767A13DD52312AB4B03310DCD7F48A9DA04FD50",
		"E8083969EDB767B0CF6095179A163AB3661A05FBD5FAAAE82918A9962F0B93B8",
		"55F97993EC975EEAA80D740ADBF4FF747359D041D5C33EA71D281E446B14773B",
		"CA97B43A23FB801676BD207A436C6481F1D2B9078717461A5B9D32E688F87748",
		"544523B524B0D57D5EA77A2775D2ECFA032CFBDBF52FB3786160279004E57AE6",
		"AF874E7303CE53299CCC041C7BC308D82A5698F3A8D0C38271AE35F8E9DBFBB6",
		"94B5C803D89F7AE435DE236D525F54759B65E372FCD68EF20FA7111F9E4AFF73",
	}, ""))
	srpG      = big.NewInt(2)
	srpLength = len(srpN.Bytes())
)

func mustHex(value string) *big.Int {
	number, ok := new(big.Int).SetString(value, 16)
	if !ok {
		panic("invalid hex: " + value)
	}

	return number
}

func sha256Of(parts ...[]byte) []byte {
	hash := sha256.New()
	for _, part := range parts {
		hash.Write(part)
	}

	return hash.Sum(nil)
}

// padded returns value left-padded with zeros to the length of N.
func padded(value []byte) []byte {
	if len(value) >= srpLength {
		return value
	}
	out := make([]byte, srpLength)
	copy(out[srpLength-len(value):], value)

	return out
}

// hashPadded is fastlane-sirp's H(): the hash of the padded values, as a
// number reduced modulo N.
func hashPadded(values ...[]byte) *big.Int {
	parts := make([][]byte, len(values))
	for index, value := range values {
		parts[index] = padded(value)
	}
	sum := new(big.Int).SetBytes(sha256Of(parts...))

	return sum.Mod(sum, srpN)
}

// passwordKey derives the value Apple's SRP uses as the password: PBKDF2 over
// the SHA-256 of the password, hex encoded first for the older s2k_fo protocol.
func passwordKey(password string, salt []byte, iterations int, protocol string) ([]byte, error) {
	digest := sha256Of([]byte(password))
	switch protocol {
	case "s2k":
	case "s2k_fo":
		digest = []byte(fmt.Sprintf("%x", digest))
	default:
		return nil, fmt.Errorf("unsupported Apple sign-in protocol %q", protocol)
	}

	return pbkdf2.Key(sha256.New, string(digest), salt, iterations, 32)
}

type srpClient struct {
	a *big.Int
	A []byte
}

func newSRPClient(random io.Reader) (*srpClient, error) {
	secret := make([]byte, 256)
	if _, err := io.ReadFull(random, secret); err != nil {
		return nil, err
	}

	return newSRPClientWith(new(big.Int).SetBytes(secret)), nil
}

func newSRPClientWith(a *big.Int) *srpClient {
	return &srpClient{a: a, A: new(big.Int).Exp(srpG, a, srpN).Bytes()}
}

// proofs computes the client proof M1 and the expected server proof M2 for
// the server's salt and B, given the derived password key.
func (c *srpClient) proofs(accountName string, key, salt, serverB []byte) ([]byte, []byte, error) {
	B := new(big.Int).SetBytes(serverB)
	if new(big.Int).Mod(B, srpN).Sign() == 0 {
		return nil, nil, errors.New("Apple sent an invalid sign-in challenge")
	}
	u := hashPadded(c.A, serverB)
	if u.Sign() == 0 {
		return nil, nil, errors.New("Apple sent an invalid sign-in challenge")
	}
	k := hashPadded(srpN.Bytes(), srpG.Bytes())
	x := new(big.Int).SetBytes(sha256Of(salt, sha256Of([]byte(":"), key)))

	// S = (B - k * g^x) ^ (a + u * x) mod N
	base := new(big.Int).Exp(srpG, x, srpN)
	base.Mul(base, k)
	base.Sub(B, base)
	base.Mod(base, srpN)
	exponent := new(big.Int).Mul(u, x)
	exponent.Add(exponent, c.a)
	S := new(big.Int).Exp(base, exponent, srpN)
	K := sha256Of(S.Bytes())

	hashN := hashPadded(srpN.Bytes())
	hashG := hashPadded(srpG.Bytes())
	xor := new(big.Int).Xor(hashN, hashG)
	M1 := sha256Of(xor.Bytes(), sha256Of([]byte(accountName)), salt, c.A, serverB, K)
	M2 := new(big.Int).SetBytes(sha256Of(c.A, M1, K)).Bytes()

	return M1, M2, nil
}

// hashcash answers Apple's proof-of-work challenge: the first counter whose
// "1:bits:date:challenge::counter" string has a SHA-1 starting with bits
// zero bits.
func hashcash(bits int, challenge string, now time.Time) string {
	prefix := "1:" + strconv.Itoa(bits) + ":" + now.Format("20060102150405") + ":" + challenge + "::"
	for counter := 0; ; counter++ {
		token := prefix + strconv.Itoa(counter)
		if leadingZeroBits(sha1.Sum([]byte(token))) >= bits {
			return token
		}
	}
}

func leadingZeroBits(sum [sha1.Size]byte) int {
	count := 0
	for _, value := range sum {
		if value == 0 {
			count += 8

			continue
		}
		for mask := byte(0x80); mask != 0 && value&mask == 0; mask >>= 1 {
			count++
		}

		break
	}

	return count
}
