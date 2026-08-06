package schema

import (
	"fmt"
	"io"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
)

// Schema changes are asynchronous. Creating an attribute answers 202 with
// status "processing"; deleting one answers immediately and the row disappears
// some time later. Nothing downstream may proceed until the change has landed
// -- an index over an attribute that is still processing is rejected, and so is
// a create against a key still being deleted -- so every phase of a push ends
// by polling the LIST endpoint until it agrees.
//
// The list endpoint is polled rather than the individual resource because a
// deletion has no resource left to get, and one paginated list answers for
// every key at once.

const (
	// pollStepSize is how many resources one default timeout is meant to cover.
	pollStepSize = 100
	// pollDebounce is the wait between polls.
	pollDebounce = 2 * time.Second
	// pollDefaultMaxDebounces is the attempt budget when --attempts is absent.
	// Also the sentinel for "the user did not choose this", which is what makes
	// the budget safe to scale below.
	pollDefaultMaxDebounces = 30
)

// Poller waits for asynchronous schema changes to land.
//
// One Poller is shared across a whole push, deliberately: the timeout it grows
// on a large collection has to stay grown for the phases that follow.
type Poller struct {
	api *client.Client
	out io.Writer
	// maxDebounces is mutable. Ports pollMaxDebounces, which pools.ts scales
	// in place the first time it sees a resource count above one step.
	maxDebounces int
	// sleep is injectable so tests do not wait out a real debounce.
	sleep func(time.Duration)
}

// NewPoller returns a poller with the given attempt budget.
//
// attempts of zero means the user did not pass --attempts, which selects the
// default AND enables the automatic scaling below.
func NewPoller(api *client.Client, out io.Writer, attempts int) *Poller {
	if attempts <= 0 {
		attempts = pollDefaultMaxDebounces
	}

	return &Poller{api: api, out: out, maxDebounces: attempts, sleep: time.Sleep}
}

// scaleTimeout grows the attempt budget once, for a resource count that cannot
// finish inside the default.
//
// Ports the `pollMaxDebounces === POLL_DEFAULT_VALUE` guard repeated in every
// pools.ts method. The comparison against the default is doing two jobs: it
// leaves an explicit --attempts alone, and because the scaling assigns through
// the same field it can only ever happen once.
func (p *Poller) scaleTimeout(iteration, count int, message string) {
	if iteration != 1 || p.maxDebounces != pollDefaultMaxDebounces {
		return
	}

	steps := max(1, (count+pollStepSize-1)/pollStepSize)
	if steps <= 1 {
		return
	}

	p.maxDebounces *= steps
	output.Log(p.out, "%s%v minutes",
		message, float64(p.maxDebounces)*pollDebounce.Minutes())
}

// ExpectAttributes waits until every key reports status "available".
//
// A key that reports "stuck" or "failed"
// is an error, not something to keep waiting on -- the API will never change it.
func (p *Poller) ExpectAttributes(container Container, keys []string) (bool, error) {
	return p.expect(container, keys, false)
}

// ExpectIndexes waits until every index reports status "available".
func (p *Poller) ExpectIndexes(container Container, keys []string) (bool, error) {
	return p.expect(container, keys, true)
}

func (p *Poller) expect(container Container, keys []string, isIndex bool) (bool, error) {
	noun, wrapper := "attributes", "attributes"
	if isIndex {
		noun, wrapper = "indexes", "indexes"
	}

	wanted := map[string]bool{}
	for _, key := range keys {
		wanted[key] = true
	}

	// No early return for an empty key set, deliberately. pools.ts still makes
	// one list request before concluding there is nothing to wait for, and a
	// request trace is how the two CLIs are compared.
	for iteration := 1; iteration <= p.maxDebounces; iteration++ {
		p.scaleTimeout(iteration, len(keys),
			fmt.Sprintf("Creating a large number of %s, increasing timeout to ", noun))

		rows, err := p.list(container, wrapper)
		if err != nil {
			return false, err
		}

		ready := 0
		for _, row := range rows {
			key := row.GetString("key")
			if !wanted[key] {
				continue
			}

			switch row.GetString("status") {
			case "stuck", "failed":
				name := "Attribute"
				if isIndex {
					name = "Index"
				}

				return false, fmt.Errorf("%s '%s' failed!", name, key)
			case "available":
				ready++
			}
		}

		if ready >= len(keys) {
			return true, nil
		}

		p.sleep(pollDebounce)
	}

	return false, nil
}

// WaitForDeletion waits until none of the keys are listed any more.
//
// Ports waitForAttributeDeletion and waitForIndexDeletion (pools.ts:98 and
// :159), which differ only in the endpoint they poll.
func (p *Poller) WaitForDeletion(container Container, keys []string, isIndex bool) (bool, error) {
	noun, wrapper := "attributes", "attributes"
	if isIndex {
		noun, wrapper = "indexes", "indexes"
	}

	for iteration := 1; iteration <= p.maxDebounces; iteration++ {
		p.scaleTimeout(iteration, len(keys),
			fmt.Sprintf("Found a large number of %s to be deleted. Increasing timeout to ", noun))

		rows, err := p.list(container, wrapper)
		if err != nil {
			return false, err
		}

		present := map[string]bool{}
		for _, row := range rows {
			present[row.GetString("key")] = true
		}

		remaining := 0
		for _, key := range keys {
			if present[key] {
				remaining++
			}
		}
		if remaining == 0 {
			return true, nil
		}

		p.sleep(pollDebounce)
	}

	return false, nil
}

// list walks one of the container's schema endpoints.
func (p *Poller) list(container Container, wrapper string) ([]*jsonx.Object, error) {
	return p.api.List(containerPath(container)+"/"+wrapper, wrapper, nil)
}
