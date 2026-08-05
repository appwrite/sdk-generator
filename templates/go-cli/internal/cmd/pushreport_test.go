package cmd

import (
	"bytes"
	"strings"
	"testing"
	"time"

	"github.com/spf13/cobra"
)

func reportOf(summary *pushSummary) string {
	var out bytes.Buffer
	command := &cobra.Command{}
	command.SetOut(&out)

	summary.report(command, sitesDeployable(), false, 43900*time.Millisecond)

	return out.String()
}

// sitesDeployable is the sites entry, found by name so the test cannot drift
// from the real labels.
func sitesDeployable() deployable {
	for _, resource := range deployables {
		if resource.Name == "site" {
			return resource
		}
	}

	panic("no site deployable")
}

// `ℹ Warning: Successfully deployed 0 of 1 sites` contradicted itself twice:
// nothing succeeded, and a push where every deployment failed is not a caveat on
// a good outcome but the bad one.
func TestNothingDeployedIsAnError(t *testing.T) {
	report := reportOf(&pushSummary{
		Pushed: 1,
		Failed: []failedDeployment{{Name: "Template site", ConsoleURL: "https://console/x"}},
	})

	if strings.Contains(report, "Successfully") {
		t.Errorf("a push that deployed nothing still claims success:\n%s", report)
	}
	if !strings.Contains(report, "✗ Error") {
		t.Errorf("a push that deployed nothing is not reported as an error:\n%s", report)
	}
	if !strings.Contains(report, "43.9s") {
		t.Errorf("the elapsed time was dropped:\n%s", report)
	}
}

// A partial result is a warning, but still not a "success" -- and how many
// failed is what the reader wants next.
func TestPartialDeploymentIsAWarningWithTheShortfall(t *testing.T) {
	report := reportOf(&pushSummary{
		Pushed:   3,
		Deployed: 2,
		Failed:   []failedDeployment{{Name: "Third site", ConsoleURL: "https://console/z"}},
	})

	if strings.Contains(report, "Successfully") {
		t.Errorf("a partial deploy claims success:\n%s", report)
	}
	if !strings.Contains(report, "Warning") {
		t.Errorf("a partial deploy is not a warning:\n%s", report)
	}
	if !strings.Contains(report, "2 of 3 sites") || !strings.Contains(report, "1 failed") {
		t.Errorf("the shortfall is not stated:\n%s", report)
	}
}

// The spinner row above the summary has already said which resource failed and
// why, so the summary states the link and nothing else. Saying `Deployment of X
// has failed` again put the same sentence on two consecutive lines and hung the
// URL off the end of the second one.
func TestAFailureIsNotAnnouncedTwice(t *testing.T) {
	report := reportOf(&pushSummary{
		Pushed: 1,
		Failed: []failedDeployment{{Name: "Template site", ConsoleURL: "https://console/x"}},
	})

	if strings.Contains(report, "has failed") {
		t.Errorf("the failure sentence is repeated in the summary:\n%s", report)
	}
	if strings.Count(report, "https://console/x") != 1 {
		t.Errorf("the deployment link is not printed exactly once:\n%s", report)
	}
}

// The link goes on its own line, which is what makes it selectable -- and it is
// the same closing line a successful deploy prints.
func TestTheDeploymentLinkIsOnItsOwnLine(t *testing.T) {
	report := reportOf(&pushSummary{
		Pushed: 1,
		Failed: []failedDeployment{{Name: "Template site", ConsoleURL: "https://console/x"}},
	})

	var found bool
	for _, line := range strings.Split(report, "\n") {
		if !strings.Contains(line, "https://console/x") {
			continue
		}
		found = true
		if !strings.Contains(line, "Deployment page:") {
			t.Errorf("the link line is not labelled: %q", line)
		}
		if strings.Contains(line, "Error") {
			t.Errorf("the link is still hung off the error sentence: %q", line)
		}
	}

	if !found {
		t.Errorf("no line carries the link:\n%s", report)
	}
}

// A timed-out deployment reports its link the same way. The spinner row said it
// got stuck; repeating that here was the same duplication.
func TestATimeoutReportsItsLinkToo(t *testing.T) {
	report := reportOf(&pushSummary{
		Pushed: 1,
		Failed: []failedDeployment{
			{Name: "Slow site", Reason: "timeout", ConsoleURL: "https://console/slow"},
		},
	})

	if !strings.Contains(report, "https://console/slow") {
		t.Errorf("a timed-out deployment lost its link:\n%s", report)
	}
	if strings.Contains(report, "got stuck") {
		t.Errorf("the timeout sentence is repeated in the summary:\n%s", report)
	}
}

// One site is a site, not `1 sites`. A count line is the last thing a push
// prints, and the agreement being wrong there reads as carelessness in the tool
// that just changed a live project.
func TestCountsAgreeWithTheirNouns(t *testing.T) {
	single := reportOf(&pushSummary{Pushed: 1,
		Failed: []failedDeployment{{Name: "One", ConsoleURL: "https://console/1"}}})
	if !strings.Contains(single, "1 site,") && !strings.Contains(single, "1 site ") {
		t.Errorf("one site was pluralised:\n%s", single)
	}

	many := reportOf(&pushSummary{Pushed: 2,
		Failed: []failedDeployment{{Name: "One", ConsoleURL: "https://console/1"}}})
	if !strings.Contains(many, "2 sites") {
		t.Errorf("two sites were singularised:\n%s", many)
	}
}

// Everything deploying is still a success, and still says so.
func TestAFullDeploymentStillReportsSuccess(t *testing.T) {
	report := reportOf(&pushSummary{Pushed: 2, Deployed: 2})

	if !strings.Contains(report, "Successfully deployed 2 sites") {
		t.Errorf("a complete deploy lost its success line:\n%s", report)
	}
}
