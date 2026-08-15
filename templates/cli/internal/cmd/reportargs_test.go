package cmd

import (
	"os"
	"strings"
	"testing"
)

const reportSecret = "standard_c8f7e6d5c4b3a29180716253443546576879"

// --report prefills an issue on a public tracker with the invocation that
// failed. Hitting an error on `client --key ...` or `login --password ...` is
// ordinary, so anything that reaches this list is one click from publication.
func TestCommandArgumentsRedactsCredentials(t *testing.T) {
	tests := []struct {
		name string
		args []string
		want []string
	}{
		{
			name: "separate value",
			args: []string{"client", "--key", reportSecret, "--report"},
			want: []string{"client", "--key", "[hidden]"},
		},
		{
			name: "value joined with =",
			args: []string{"client", "--key=" + reportSecret},
			want: []string{"client", "--key=[hidden]"},
		},
		{
			name: "shorthand with separate value",
			args: []string{"client", "-k", reportSecret},
			want: []string{"client", "-k", "[hidden]"},
		},
		{
			name: "shorthand with attached value",
			args: []string{"client", "-k" + reportSecret},
			want: []string{"client", "-k[hidden]"},
		},
		{
			name: "shorthand with = value",
			args: []string{"client", "-k=" + reportSecret},
			want: []string{"client", "-k=[hidden]"},
		},
		{
			name: "password",
			args: []string{"login", "--password", "hunter2"},
			want: []string{"login", "--password", "[hidden]"},
		},
		{
			name: "a suffix the shared list misses",
			args: []string{"users", "create-argon-user", "--password-signer-key", "pepper"},
			want: []string{"users", "create-argon-user", "--password-signer-key", "[hidden]"},
		},
		{
			name: "ordinary flags survive intact",
			args: []string{"push", "function", "--function-id", "abc", "--force"},
			want: []string{"push", "function", "--function-id", "abc", "--force"},
		},
		{
			name: "a positional holding = is not a flag",
			args: []string{"functions", "create-variable", "--value", "A=B"},
			want: []string{"functions", "create-variable", "--value", "A=B"},
		},
		{
			name: "--report=true is dropped too",
			args: []string{"client", "--report=true", "--key", reportSecret},
			want: []string{"client", "--key", "[hidden]"},
		},
	}

	original := os.Args
	t.Cleanup(func() { os.Args = original })

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			os.Args = append([]string{"appwrite"}, test.args...)

			got := commandArguments()
			if strings.Join(got, " ") != strings.Join(test.want, " ") {
				t.Errorf("commandArguments()\n got %v\nwant %v", got, test.want)
			}
			for _, argument := range got {
				if strings.Contains(argument, reportSecret) || argument == "hunter2" {
					t.Errorf("credential survived into the report: %q", argument)
				}
			}
		})
	}
}

// The URL is what actually gets published, so assert on it rather than trusting
// that every caller of commandArguments stays careful.
func TestReportURLCarriesNoCredential(t *testing.T) {
	original := os.Args
	t.Cleanup(func() { os.Args = original })
	os.Args = []string{"appwrite", "client", "--key", reportSecret, "--report"}

	url := ReportURL(errStub{})

	if url == "" {
		t.Fatal("ReportURL returned nothing")
	}
	if strings.Contains(url, reportSecret) {
		t.Errorf("ReportURL leaked the credential:\n%s", url)
	}
	// The credential is escaped inside a query string, so also check the form the
	// encoder produces -- a raw substring test alone could pass while leaking.
	if strings.Contains(url, "standard_") {
		t.Errorf("ReportURL leaked a credential prefix:\n%s", url)
	}
}

type errStub struct{}

func (errStub) Error() string { return "something went wrong" }
