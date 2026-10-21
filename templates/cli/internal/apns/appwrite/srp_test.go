package appwrite

import (
	"bytes"
	"context"
	"encoding/base64"
	"encoding/hex"
	"errors"
	"math/big"
	"testing"
	"time"
)

// The expected values were computed with fastlane-sirp and spaceship's
// hashcash, the Ruby code spaceship signs in to Apple with, for fixed inputs.
var srpVectors = []struct {
	protocol, password                         string
	iterations                                 int
	a, clientA, salt, serverB, derived, m1, m2 string
}{
	{
		protocol:   "s2k",
		password:   "correct horse battery staple",
		iterations: 1000,
		a:          "976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c976c0718d60c26f9f0c222b52a8e47282b714657aebb3e224000792ec3e9054c",
		clientA:    "lOFBKFUcFnIqiPacVzlN2auN4I/AMJTWac7+/T1qEp2nhqIlgqfiFMgS/lLgf2YHa/88G9823FwFSNcRw/h46ujunwE/pkwZKFscpaa68psSBryNQFK1HsWrHblsQdOicXq+dmD+p70d1tx6w935hE8Htq+3iFdyqvb/nrau/qdyKJ1ueEWpnJ71r35XmHdfVQ94/qAsOp753gLCRFNdTmvFONzkqS9PRzC3Ug9F0PKeMf1kQhurzoMZRo/HqS+Pz7bTdDW5efKMg4s6uvX7t+LffwUwxuoCP/NksHQyRznffQivjnh596NJ4XCrEpvu+tE4bOBjtlSYSYI45JQmrw==",
		salt:       "8XJskbjIRdqLv44iq8fuiQ==",
		serverB:    "bmqruv0a/EiZ7xJfeLeG1ilXyXzhUJNYtDmwpbgK152y+6RctWvwBHLLnTVQlfHYzKIgMrlChKSyCl6tXjOkKz73r03bRCUL675sZDN7K3pGEL2TvrU/qdb7XBgKUWI1ctW54xynuBy1YpSYoETwrrHirVB3LDKlBdp+J3T+MfuJeR270cGJ/nDKAXtfj3qwagSrM17wI193qe4jEevZD4mvlKztv29zQ6/T9q6vTopJ524kZTzcY00t0y9r7jVFzpRXXMfg5hCzrNdKkECjFpC6/UI/xDKmyl/Uux8yG5dA5Gcxcsb0GfT7g01i3sdd619gUlceFifCpvI56NIapQ==",
		derived:    "adb53b51adb7d706507d630b73e0d85ec46c34ffa95eabc6bfb6893092bfe538",
		m1:         "T02D0QeodD+9ldyfjZQUTynd976L9gg52UBkxEFXRjE=",
		m2:         "bkDJQIvXJOIj1/cMlHJfnab9VrMyBSzaVNJY4kdCik0=",
	},
	{
		protocol:   "s2k_fo",
		password:   "p@ss wörd",
		iterations: 1001,
		a:          "62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b62d6b738b14920953248eb84424d1a0a78c738c0a95e421e7f86ae320348068b",
		clientA:    "q3Y06Eo7PTQ8TvzRw5xZqRwCKcw0GTxg5+6b5a75N+Dy3rnNXSAM0/aPomhpAcHMz14FtSALPmpNXEYa3UxUUGWiLkp1RDLTzdJT2U/+X0lU6XBm5W0O8hyYe3/Na8JrQvNvsDfmahk4/w8ZKKDTSq1cbRiQHWV7wA/D5tl5jh/xPADahOe4jt3rU+COrHR3JcVDWknM0B4cL0HywA2J2/RrK/ushhR/E8Uu7wGvb7LbcFNhOmp4q6q9bLrm5zS6tbH6dXfD3V5W4bvgFOwhKKmS0B39v4sZZRSkHiTdLMAipBgShP5kPAi1S0RoRXFYD1sHI2v7m0mP5P+S45wI",
		salt:       "lneDgZnxLTb17pcdSfaKvA==",
		serverB:    "Ht8tUspsId2o6YnOxb/QKSLQ3vn0scNeYsV7ElRHRmXKj3b6neErfO7BCpQFvY59g1hvwwDT2fFa4Ah2nzLjRHG83W/vA/Jt0MltHSECjhV0ASfMHfMQVA6VlDLNAdAO8e4weK49v9j/AWobmdXQjBkSRTuTzMT4rKjXlmXdT54Mo3ryCPxhA2r4u+FYV4qn2vaW2GBKAj2ONMZQW0DWV90LhnnQZ6mib/mpUctOv+rTZ893ZbtytYByZtwnd91AV1QV55KNOmEqRrJEXlb3oqMVLR0FQyai2kZVH5yIT6Jri/wTfKKNkl1vIYsbM5ctc0k7UdADQYQ8070Bd1k0mQ==",
		derived:    "296173526d4ff2ba32b6027b25166623f12566dad89b707af650e146628e95b4",
		m1:         "Gfm875AAWz5Rh4ok/WHAzVuVqKPG6jwLnf/5OGyl+H4=",
		m2:         "XxfUcq4O5mBX/SC9s3QCv8nU0fuz5R47opb5rO9JDzE=",
	},
}

func decode(t *testing.T, value string) []byte {
	t.Helper()
	decoded, err := base64.StdEncoding.DecodeString(value)
	if err != nil {
		t.Fatal(err)
	}

	return decoded
}

func TestSRPMatchesFastlane(t *testing.T) {
	for _, vector := range srpVectors {
		t.Run(vector.protocol, func(t *testing.T) {
			salt := decode(t, vector.salt)
			key, err := passwordKey(vector.password, salt, vector.iterations, vector.protocol)
			if err != nil || hex.EncodeToString(key) != vector.derived {
				t.Fatalf("password key = %x, %v", key, err)
			}

			a, _ := new(big.Int).SetString(vector.a, 16)
			client := newSRPClientWith(a)
			if !bytes.Equal(client.A, decode(t, vector.clientA)) {
				t.Errorf("A = %s", base64.StdEncoding.EncodeToString(client.A))
			}
			m1, m2, err := client.proofs("dev@example.com", key, salt, decode(t, vector.serverB))
			if err != nil {
				t.Fatal(err)
			}
			if got := base64.StdEncoding.EncodeToString(m1); got != vector.m1 {
				t.Errorf("m1 = %s, want %s", got, vector.m1)
			}
			if got := base64.StdEncoding.EncodeToString(m2); got != vector.m2 {
				t.Errorf("m2 = %s, want %s", got, vector.m2)
			}
		})
	}
}

func TestSRPRejectsAnInvalidChallenge(t *testing.T) {
	client := newSRPClientWith(big.NewInt(12345))
	if _, _, err := client.proofs("dev@example.com", []byte("key"), []byte("salt"), srpN.Bytes()); err == nil {
		t.Error("accepted B = N")
	}
	if _, err := passwordKey("password", []byte("salt"), 1, "s2k_unknown"); err == nil {
		t.Error("accepted an unknown protocol")
	}
}

func TestHashcashStopsWhenCancelled(t *testing.T) {
	ctx, cancel := context.WithCancel(context.Background())
	cancel()
	if token, err := hashcash(ctx, 64, "challenge", time.Now()); !errors.Is(err, context.Canceled) || token != "" {
		t.Errorf("hashcash = %q, %v", token, err)
	}
}

func TestHashcashUsesUTC(t *testing.T) {
	utc := time.Date(2023, 2, 23, 17, 6, 0, 0, time.UTC)
	india := utc.In(time.FixedZone("IST", 5*3600+1800))
	got, _ := hashcash(context.Background(), 11, "4d74fb15eb23f465f1f6fcbf534e5877", india)
	want, _ := hashcash(context.Background(), 11, "4d74fb15eb23f465f1f6fcbf534e5877", utc)
	if got != want {
		t.Errorf("hashcash in IST = %s, in UTC = %s", got, want)
	}
}

func TestHashcashMatchesSpaceship(t *testing.T) {
	for _, vector := range []struct {
		bits                   int
		challenge, date, token string
	}{
		{11, "4d74fb15eb23f465f1f6fcbf534e5877", "20230223170600", "1:11:20230223170600:4d74fb15eb23f465f1f6fcbf534e5877::6373"},
		{10, "abc", "20261009120000", "1:10:20261009120000:abc::1266"},
	} {
		date, err := time.Parse("20060102150405", vector.date)
		if err != nil {
			t.Fatal(err)
		}
		if got, err := hashcash(context.Background(), vector.bits, vector.challenge, date); err != nil || got != vector.token {
			t.Errorf("hashcash(%d, %s) = %s, want %s", vector.bits, vector.challenge, got, vector.token)
		}
	}
}
