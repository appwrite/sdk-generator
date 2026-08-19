package cmd

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/url"
	"os"
	"path/filepath"
	"runtime"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
	"github.com/spf13/cobra"
)

// apiError is what the SDK's AppwriteError provides.
//
// Matched as an interface rather than as *client.AppwriteError because the
// SDK's fields are unexported, so nothing outside that package can build one --
// including a test for this function.
type apiError interface {
	error
	GetMessage() string
	GetStatusCode() int
}

// When a response is not JSON the SDK puts the whole body in the error message,
// and the CLI printed it. A proxy 502, a WAF block or a maintenance page is an
// HTML document, so `users get` answered with 8,946 bytes of markup where one
// line would do.
//
// The body is still available -- it is what --verbose is for -- but it is not
// the first thing a user should see.

// maximumMessageLength is where a "message" stops being one.
//
// Comfortably longer than any message the API sends and far shorter than a
// rendered error page.
const maximumMessageLength = 400

// actionableError carries a known recovery path without baking terminal layout
// into Error(). Logs and reports retain the ordinary message; Report can render
// the title, facts and command as a readable block.
type actionableError struct {
	cause   error
	title   string
	details []output.FailureDetail
	action  string
	command string
}

func (e *actionableError) Error() string { return e.cause.Error() }
func (e *actionableError) Unwrap() error { return e.cause }

func endpointMismatchError(projectEndpoint, sessionEndpoint string) error {
	switchEndpoint := config.NormalizeCloudConsoleEndpoint(projectEndpoint)
	environment := "the project environment"
	if base, cloud := config.CloudBaseHost(projectEndpoint); cloud {
		environment = "Appwrite Cloud"
		if strings.Contains(base, "staging") {
			environment = "Appwrite Cloud Staging"
		}
	}

	cause := fmt.Errorf("project endpoint %s does not match active session endpoint %s",
		projectEndpoint, sessionEndpoint)
	return &actionableError{
		cause: cause,
		title: "Active session doesn’t match this project",
		details: []output.FailureDetail{
			{Label: "Project endpoint", Value: projectEndpoint},
			{Label: "Active session", Value: sessionEndpoint},
		},
		action: "Switch to " + environment + ":",
		command: fmt.Sprintf("%s login --switch --endpoint %s",
			app.ExecutableName, switchEndpoint),
	}
}

func missingProjectConfigError(err error) *actionableError {
	var pathError *os.PathError
	if !errors.As(err, &pathError) || !errors.Is(err, os.ErrNotExist) ||
		filepath.Base(pathError.Path) != config.LocalFileName {
		return nil
	}

	return &actionableError{
		cause: err,
		title: "Appwrite project configuration not found",
		details: []output.FailureDetail{
			{Label: "Expected file", Value: pathError.Path},
		},
		action: "Run this command from a directory containing " + config.LocalFileName +
			", or initialize a project:",
		command: app.ExecutableName + " init project",
	}
}

// FormatError renders a command failure for the terminal.
func FormatError(err error) string {
	if err == nil {
		return ""
	}

	var appwrite apiError
	if !errors.As(err, &appwrite) {
		return err.Error()
	}

	message := appwrite.GetMessage()
	if !isDocument(message) {
		return message
	}

	kind := documentKind(message)
	article := "a"
	if strings.ContainsRune("AEIOU", rune(kind[0])) {
		article = "an"
	}

	summary := fmt.Sprintf(
		"the server returned %d with %s %s response instead of JSON",
		appwrite.GetStatusCode(), article, kind)

	if app.Flags().Verbose {
		return summary + "\n\n" + message
	}

	return summary + ". Re-run with --verbose to see it"
}

// isDocument reports whether a message is really a response body.
func isDocument(message string) bool {
	trimmed := strings.TrimSpace(message)

	return strings.HasPrefix(trimmed, "<") || len(trimmed) > maximumMessageLength
}

// documentKind names the body in the summary, so the line says something
// useful about what came back.
func documentKind(message string) string {
	lowered := strings.ToLower(strings.TrimSpace(message))
	if strings.HasPrefix(lowered, "<!doctype html") || strings.HasPrefix(lowered, "<html") {
		return "HTML"
	}
	if strings.HasPrefix(lowered, "<") {
		return "markup"
	}

	return "long text"
}

// maximumReportTitleLength keeps a summarised message from dominating the
// issue title. Ports MAX_REPORT_TITLE_LENGTH.
const maximumReportTitleLength = 100

// ReportURL builds a prefilled GitHub issue for a failed command.
//
// CLI releases tell the
// user to "pass the --verbose or --report flag" on every error; in Go the
// second half of that sentence was a lie, because the flag did not exist.
func ReportURL(err error) string {
	if err == nil {
		return ""
	}

	message := FormatError(err)

	environment := fmt.Sprintf("CLI version: %s\nOperation System: %s\nArchitecture: %s",
		app.Version, runtime.GOOS, runtime.GOARCH)

	query := url.Values{}
	query.Set("labels", "bug")
	query.Set("template", "bug.yaml")
	query.Set("title", "🐛 Bug Report: "+truncate(message, maximumReportTitleLength))
	query.Set("actual-behavior", "CLI Error:\n```\n"+truncate(message, 2000)+"\n```")
	query.Set("steps-to-reproduce",
		"Running `"+app.ExecutableName+" "+strings.Join(commandArguments(), " ")+"`")
	query.Set("environment", environment)

	return "https://github.com/appwrite/appwrite/issues/new?" + query.Encode()
}

// ReportBlock is what the CLI prints under an error when --report is given.
func ReportBlock(err error) string {
	return "To report this error you can:\n" +
		" - Create a support ticket in our Discord server https://appwrite.io/discord\n" +
		" - Create an issue in our Github\n   " + ReportURL(err)
}

// RequiredArgument validates a command that takes exactly one argument, and says
// which one when it is missing. cobra's own `accepts 1 arg(s), received 0` names
// neither the argument nor the command; description is the argument's help text,
// which is what turns the error into an instruction.
func RequiredArgument(name, description string) cobra.PositionalArgs {
	return func(command *cobra.Command, arguments []string) error {
		if len(arguments) == 1 {
			return nil
		}

		if len(arguments) > 1 {
			return fmt.Errorf(
				"`%s` takes one argument, %s, and was given %d. Run `%s --help` for the usage",
				command.CommandPath(), name, len(arguments), command.CommandPath())
		}

		return fmt.Errorf("missing %s -- %s. Run `%s %s`",
			name, strings.ToLower(description), command.CommandPath(), name)
	}
}

// ExitCancelled is the status a cancelled prompt exits with: 128 + SIGINT,
// which is what a shell reports for a program the user interrupted.
//
// Distinct from 1 on purpose. Answering "no" to a prompt, or walking away from
// one, is not the same outcome as a command that failed, and a script driving
// the CLI should be able to tell them apart.
const ExitCancelled = 130

// IsCancelled reports whether a command stopped because the user cancelled a
// prompt rather than because anything went wrong.
func IsCancelled(err error) bool {
	return errors.Is(err, prompt.ErrAborted)
}

// Report renders a command failure and returns the status the process should
// exit with.
//
// Lives here rather than in main so it can be exercised: the difference between
// what a user sees for a cancelled prompt and for a failed request is the whole
// point of it, and main() is not reachable from a test.
func Report(writer io.Writer, executed *cobra.Command, err error) int {
	if err == nil {
		return 0
	}

	if IsCancelled(err) {
		// Note, not Warn: nothing is wrong. Prefixing a deliberate Ctrl-C with
		// "Warning" told the user off for their own decision.
		output.Note(writer, "%s", CancellationNotice(executed))

		return ExitCancelled
	}

	// Skipped once the user has already asked for the detail it points at, and
	// when there is no detail to ask for: sdk.RequestWasMade is what makes the
	// advice true, because everything --verbose adds comes from a request.
	if sdk.RequestWasMade() && !detailWasRequested() {
		output.Log(writer, "For detailed error pass the --verbose or --report flag")
	}

	var actionable *actionableError
	if errors.As(err, &actionable) {
		output.ActionableFailure(writer, actionable.title, actionable.details,
			actionable.action, actionable.command)
	} else if missing := missingProjectConfigError(err); missing != nil {
		output.ActionableFailure(writer, missing.title, missing.details,
			missing.action, missing.command)
	} else {
		output.Failure(writer, "%s", FormatError(err))
	}

	// --verbose has to add the response body: on the failure it matters most for
	// -- a response the SDK could not decode -- a message naming a field and a
	// type gives no way to see what actually arrived.
	if app.Flags().Verbose {
		if detail := ErrorDetail(err); detail != "" {
			fmt.Fprint(writer, detail)
		}
	}

	if app.Flags().Report {
		fmt.Fprintln(writer, ReportBlock(err))
	}

	return 1
}

// ErrorDetail is everything known about a failure, for --verbose.
//
// A Go error carries no stack, so the unwrap chain stands in for one. The
// response body is the reason this exists: it is the only thing that says
// whether the API changed shape or the CLI has the model wrong.
func ErrorDetail(err error) string {
	if err == nil {
		return ""
	}

	var builder strings.Builder
	builder.WriteString("\n" + output.Heading("Error detail") + "\n\n")

	fmt.Fprintf(&builder, "  %-10s %T\n", "type:", err)

	var appwrite apiError
	if errors.As(err, &appwrite) {
		fmt.Fprintf(&builder, "  %-10s %d\n", "status:", appwrite.GetStatusCode())
	}

	// The chain, outermost first, skipping the layer already printed as the
	// message above.
	for wrapped := errors.Unwrap(err); wrapped != nil; wrapped = errors.Unwrap(wrapped) {
		fmt.Fprintf(&builder, "  %-10s %s\n", "wrapped:", wrapped)
	}

	if response := sdk.LastResponse.Take(); len(response) > 0 {
		builder.WriteString("\n" + output.Heading("Response") + "\n\n")
		builder.WriteString(indentBlock(prettyJSON(response)))
	}

	return builder.String() + "\n"
}

// arrayPreview is how many elements of a long array --verbose prints.
//
// Two, because the question a reader has is what SHAPE the elements are -- one
// is enough to answer it and the second confirms it was not a fluke. `project
// get-usage` over a year answers with fourteen metrics of 365 entries each: six
// thousand lines to say "an array of {value, date}".
const arrayPreview = 2

// prettyJSON re-indents a body and collapses its long arrays. A body that is not
// JSON is printed untouched -- an HTML error page or a proxy's refusal is the
// whole diagnosis. Arrays are collapsed rather than lines truncated, because the
// field that failed to decode can sit anywhere in the response.
func prettyJSON(body []byte) string {
	decoder := json.NewDecoder(bytes.NewReader(body))
	// Numbers stay as written: an id that arrived as 20 digits must not be
	// reprinted in scientific notation by the tool explaining what arrived.
	decoder.UseNumber()

	var builder strings.Builder
	if err := writeJSONValue(decoder, &builder, 0, ""); err != nil {
		// Not JSON, or truncated mid-stream. Either way the bytes are the
		// evidence, so they are printed as they came.
		var indented bytes.Buffer
		if json.Indent(&indented, body, "", "  ") == nil {
			return indented.String()
		}

		return string(body)
	}

	return builder.String()
}

func jsonIndent(depth int) string { return strings.Repeat("  ", depth) }

// writeJSONValue re-emits one value in the order it was read, from tokens rather
// than a map, which would lose key order. key is the field the value sits under
// so credentials can be masked -- threaded through arrays too, since an array
// under a sensitive key is a list of credentials.
func writeJSONValue(decoder *json.Decoder, builder *strings.Builder, depth int, key string) error {
	token, err := decoder.Token()
	if err != nil {
		return err
	}

	delimiter, isDelimiter := token.(json.Delim)
	if !isDelimiter {
		if text, isText := token.(string); isText {
			token = maskJSONString(text, key)
		}
		encoded, err := json.Marshal(token)
		if err != nil {
			return err
		}
		builder.Write(encoded)

		return nil
	}

	switch delimiter {
	case '{':
		// A nested object's own field names govern its values, so the key does
		// not carry any further down.
		return writeJSONObject(decoder, builder, depth)
	case '[':
		return writeJSONArray(decoder, builder, depth, key)
	default:
		return fmt.Errorf("unexpected %v", delimiter)
	}
}

// maskJSONString redacts a value --verbose would otherwise print in full.
//
// The captured body reaches the terminal through a path that never built a
// Redactor, so `project create-key --verbose` printed the live secret that the
// normal render path masks. Same bytes, two paths -- this closes the second one.
// --show-secrets is honoured, exactly as it is on the first.
func maskJSONString(text, key string) string {
	if key == "" || app.Flags().ShowSecrets || !output.IsSensitiveKey(key) {
		return text
	}

	return output.MaskString(text, key)
}

func writeJSONObject(decoder *json.Decoder, builder *strings.Builder, depth int) error {
	builder.WriteString("{")

	empty := true
	for first := true; decoder.More(); first = false {
		empty = false
		if !first {
			builder.WriteString(",")
		}

		key, err := decoder.Token()
		if err != nil {
			return err
		}
		encoded, err := json.Marshal(key)
		if err != nil {
			return err
		}

		builder.WriteString("\n" + jsonIndent(depth+1))
		builder.Write(encoded)
		builder.WriteString(": ")

		keyText, _ := key.(string)
		if err := writeJSONValue(decoder, builder, depth+1, keyText); err != nil {
			return err
		}
	}

	if _, err := decoder.Token(); err != nil {
		return err
	}

	// `{}` on one line, not an empty pair of braces two lines apart, which is
	// how an empty object read before -- as though a field had been cut.
	if !empty {
		builder.WriteString("\n" + jsonIndent(depth))
	}
	builder.WriteString("}")

	return nil
}

func writeJSONArray(decoder *json.Decoder, builder *strings.Builder, depth int, key string) error {
	builder.WriteString("[")

	total := 0
	for ; decoder.More(); total++ {
		// Past the preview the elements are still DECODED -- the tokens have to
		// be consumed to reach the closing bracket -- just not kept.
		target := builder
		if total >= arrayPreview {
			target = &strings.Builder{}
		}

		if total > 0 && total < arrayPreview {
			builder.WriteString(",")
		}
		if total < arrayPreview {
			builder.WriteString("\n" + jsonIndent(depth+1))
		}

		if err := writeJSONValue(decoder, target, depth+1, key); err != nil {
			return err
		}
	}

	if _, err := decoder.Token(); err != nil {
		return err
	}

	if omitted := total - arrayPreview; omitted > 0 {
		// Not valid JSON, deliberately: this block is read by a person deciding
		// whether the response matches the model, and the count is the part a
		// truncation must not hide.
		fmt.Fprintf(builder, ",\n%s... %d more of %d",
			jsonIndent(depth+1), omitted, total)
	}
	if total > 0 {
		builder.WriteString("\n" + jsonIndent(depth))
	}
	builder.WriteString("]")

	return nil
}

// indentBlock shifts a block two spaces right, to sit under its heading.
func indentBlock(text string) string {
	lines := strings.Split(strings.TrimRight(text, "\n"), "\n")
	for index, line := range lines {
		lines[index] = "  " + line
	}

	return strings.Join(lines, "\n") + "\n"
}

// detailWasRequested reports whether the user asked for the detail the advice
// line points at.
//
// The parsed flags are not enough. A command that fails DURING parsing never
// reaches the later flags: `appwrite users get --bogus --verbose` stops at
// --bogus, so Verbose is still false and the CLI advised passing a flag that is
// already in the invocation. The arguments are what the user actually typed.
func detailWasRequested() bool {
	if app.Flags().Verbose || app.Flags().Report {
		return true
	}

	for _, argument := range os.Args[1:] {
		// A `--` ends the flags; anything after it is a value.
		if argument == "--" {
			return false
		}
		if argument == "--verbose" || argument == "--report" {
			return true
		}
		// -V, and clusters such as -jV. Not a long flag, and not a bare "-".
		if len(argument) > 1 && argument[0] == '-' && argument[1] != '-' &&
			strings.ContainsRune(argument, 'V') {
			return true
		}
	}

	return false
}

// CancellationNotice is the line printed for a cancelled prompt.
//
// Names the command, because a prompt can be several screens into a flow --
// `push` asks three questions -- so "Cancelled." alone leaves the user to work
// out what they just abandoned. The command's PATH, not the arguments: those
// can carry an API key, and this line may end up in a log.
func CancellationNotice(command *cobra.Command) string {
	if command == nil {
		return "Cancelled. Nothing further was sent."
	}

	return fmt.Sprintf("Cancelled `%s`. Nothing further was sent.", command.CommandPath())
}

// commandArguments is the invocation, minus the flag that asked for the report
// and minus any credential the invocation carried.
//
// This goes into a prefilled issue body on a public tracker, so a credential
// flag's value is replaced rather than quoted. `client --key standard_...` and
// `login --password ...` are ordinary things to hit an error on and then report,
// which put a live credential one click from being published.
func commandArguments() []string {
	arguments := make([]string, 0, len(os.Args))
	redactValue := false
	for _, argument := range os.Args[1:] {
		if argument == "--report" || strings.HasPrefix(argument, "--report=") {
			continue
		}

		if redactValue {
			arguments = append(arguments, output.HiddenValue)
			redactValue = false

			continue
		}

		isFlag := strings.HasPrefix(argument, "-")

		// One token carrying both: `--key=secret`, `-k=secret`.
		if name, _, found := strings.Cut(argument, "="); found && isFlag {
			if output.IsSensitiveFlagName(strings.TrimLeft(name, "-")) {
				arguments = append(arguments, name+"="+output.HiddenValue)

				continue
			}
			arguments = append(arguments, argument)

			continue
		}

		// A shorthand can carry its value attached, with no separator: `-ksecret`.
		if isFlag && !strings.HasPrefix(argument, "--") && len(argument) > 2 &&
			output.IsSensitiveFlagName(argument[1:2]) {
			arguments = append(arguments, argument[:2]+output.HiddenValue)

			continue
		}

		// A bare credential flag takes the next token as its value.
		if isFlag && output.IsSensitiveFlagName(strings.TrimLeft(argument, "-")) {
			redactValue = true
		}

		arguments = append(arguments, argument)
	}

	return arguments
}

// truncate shortens text to a limit, on a rune boundary.
func truncate(text string, limit int) string {
	runes := []rune(text)
	if len(runes) <= limit {
		return text
	}

	return string(runes[:limit]) + "..."
}
