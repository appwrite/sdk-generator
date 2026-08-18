package docker

import (
	"context"
	"errors"
	"fmt"
	"io"
	"net"
	"os"
	"os/exec"
	"strconv"
	"sync"
	"syscall"
	"time"
)

// Every call shells out to the `docker` binary rather than speaking to the
// daemon socket: it inherits the user's Docker context, credentials and
// remote-host
// configuration for free, all of which a direct socket client would have to
// reimplement to work on Docker Desktop, Colima and a remote engine alike.

// hintsOff suppresses Docker's interactive CLI hints, which would otherwise
// interleave with function output.
const hintsOff = "DOCKER_CLI_HINTS=false"

// Client runs docker commands.
type Client struct {
	// Binary is the docker executable. Empty means look it up on PATH.
	Binary string
	// Stdout and Stderr receive container output.
	Stdout io.Writer
	Stderr io.Writer
}

// Available reports whether docker is on PATH.
func (c *Client) Available() bool {
	_, err := exec.LookPath(c.binary())

	return err == nil
}

func (c *Client) binary() string {
	if c.Binary != "" {
		return c.Binary
	}

	return "docker"
}

// command builds a docker invocation with the CLI hints suppressed.
func (c *Client) command(ctx context.Context, arguments ...string) *exec.Cmd {
	command := exec.CommandContext(ctx, c.binary(), arguments...)
	command.Env = append(os.Environ(), hintsOff)

	return command
}

// ExitError reports a docker process that did not exit cleanly.
type ExitError struct {
	Context string
	Code    int
	Signal  string
}

func (e *ExitError) Error() string {
	if e.Signal != "" {
		return fmt.Sprintf("%s Docker process exited with signal %s.", e.Context, e.Signal)
	}

	return fmt.Sprintf("%s Docker process exited with code %d.", e.Context, e.Code)
}

// describe turns a process result into an ExitError, or nil on success.
func describe(err error, context string) error {
	if err == nil {
		return nil
	}

	var exitError *exec.ExitError
	if errors.As(err, &exitError) {
		status := exitError.ProcessState
		if signal := signalName(status); signal != "" {
			return &ExitError{Context: context, Signal: signal}
		}

		return &ExitError{Context: context, Code: status.ExitCode()}
	}

	return fmt.Errorf("%s %w", context, err)
}

// Stop removes a container, ignoring the outcome.
//
// Called on every path including cleanup, where the container may already be
// gone; a failure here must never mask the error that led to the cleanup.
func (c *Client) Stop(ctx context.Context, id string) {
	command := c.command(ctx, "rm", "--force", id)
	_ = command.Run()
}

// Pull fetches a runtime image.
func (c *Client) Pull(ctx context.Context, image string) error {
	command := c.command(ctx, "pull", image)

	return describe(command.Run(), fmt.Sprintf("Unable to pull Docker image '%s'.", image))
}

// environmentArguments names the variables to forward, without their values.
//
// Bare `-e KEY` makes docker read the value from its own environment. `-e
// KEY=VALUE` would put a live project key and user JWT on the command line,
// which is world-readable through /proc/<pid>/cmdline; /proc/<pid>/environ is
// readable only by its owner. Order comes from keys so the command is stable.
func environmentArguments(keys []string) []string {
	arguments := make([]string, 0, len(keys)*2)
	for _, key := range keys {
		arguments = append(arguments, "-e", key)
	}

	return arguments
}

// environmentEntries renders the same variables as KEY=VALUE, for the docker
// process's own environment rather than for its command line.
//
// Appended after os.Environ() deliberately: exec resolves a duplicate key to the
// last entry, so a variable of the same name in the CLI's environment does not
// shadow the one the function asked for.
func environmentEntries(keys []string, variables map[string]string) []string {
	entries := make([]string, 0, len(keys))
	for _, key := range keys {
		entries = append(entries, key+"="+variables[key])
	}

	return entries
}

// BuildOptions is one function build.
type BuildOptions struct {
	ID string
	// StagePath holds the copied sources; it is bind-mounted at /mnt/code.
	StagePath  string
	Image      string
	Entrypoint string
	Commands   string
	// WorkingDirectory is the function directory the docker process runs from.
	WorkingDirectory string
	VariableKeys     []string
	Variables        map[string]string
	// Cancelled is polled while the build runs; returning true kills it. This
	// is how an edit made mid-build aborts a build that is already stale.
	Cancelled func() bool
}

// Build compiles a function inside its runtime image.
//
// Returns cancelled=true when Cancelled fired, in which case no error is
// reported: a build abandoned on purpose is not a failure.
func (c *Client) Build(ctx context.Context, options BuildOptions) (cancelled bool, err error) {
	arguments := []string{"run", "--name", options.ID,
		"-v", options.StagePath + "/:/mnt/code:rw",
		"-e", "OPEN_RUNTIMES_ENV=development",
		"-e", "OPEN_RUNTIMES_SECRET=",
		"-e", "OPEN_RUNTIMES_ENTRYPOINT=" + options.Entrypoint,
	}
	arguments = append(arguments, environmentArguments(options.VariableKeys)...)
	arguments = append(arguments, options.Image, "sh", "-c",
		fmt.Sprintf(`helpers/build.sh "%s"`, quoteShellArgument(options.Commands)))

	buildContext, cancel := context.WithCancel(ctx)
	defer cancel()

	command := c.command(buildContext, arguments...)
	command.Env = append(command.Env, environmentEntries(options.VariableKeys, options.Variables)...)
	command.Dir = options.WorkingDirectory
	command.Stdout = c.Stdout
	command.Stderr = c.Stderr

	if err := command.Start(); err != nil {
		return false, err
	}

	// Polled rather than event-driven because the signal is "a file changed",
	// which arrives on a different goroutine and has no channel here.
	var aborted bool
	var once sync.Once
	done := make(chan struct{})

	if options.Cancelled != nil {
		go func() {
			ticker := time.NewTicker(100 * time.Millisecond)
			defer ticker.Stop()

			for {
				select {
				case <-done:
					return
				case <-ticker.C:
					if options.Cancelled() {
						once.Do(func() { aborted = true })
						cancel()

						return
					}
				}
			}
		}()
	}

	waitErr := command.Wait()
	close(done)

	if aborted {
		c.Stop(context.WithoutCancel(ctx), options.ID)

		return true, nil
	}

	return false, describe(waitErr, "Unable to build function.")
}

// CopyOut copies a path out of a stopped container.
func (c *Client) CopyOut(ctx context.Context, id, containerPath, hostPath, workingDirectory string) error {
	command := c.command(ctx, "cp", id+":"+containerPath, hostPath)
	command.Dir = workingDirectory

	return describe(command.Run(), "Unable to copy built bundle.")
}

// StartOptions is one function container.
type StartOptions struct {
	ID    string
	Image string
	Port  int
	// FunctionDirectory holds the .appwrite directory bind-mounted for logs
	// and the built bundle.
	FunctionDirectory string
	Entrypoint        string
	StartCommand      string
	VariableKeys      []string
	Variables         map[string]string
}

// Start runs a built function and waits until it is serving.
//
// Returns once the port accepts a connection. The container keeps running; the
// returned wait function blocks until it exits.
func (c *Client) Start(ctx context.Context, options StartOptions) (wait func() error, err error) {
	scratch := func(name string) string {
		return options.FunctionDirectory + "/" + AppwriteDirectory + "/" + name
	}

	arguments := []string{"run", "--rm", "--name", options.ID,
		"-p", strconv.Itoa(options.Port) + ":3000",
		"-e", "OPEN_RUNTIMES_ENV=development",
		"-e", "OPEN_RUNTIMES_SECRET=",
		"-e", "OPEN_RUNTIMES_ENTRYPOINT=" + options.Entrypoint,
	}
	arguments = append(arguments, environmentArguments(options.VariableKeys)...)
	arguments = append(arguments,
		"-v", scratch("logs.txt")+":/mnt/logs/dev_logs.log:rw",
		"-v", scratch("errors.txt")+":/mnt/logs/dev_errors.log:rw",
		"-v", scratch("build.tar.gz")+":/mnt/code/code.tar.gz:ro",
		options.Image, "sh", "-c",
		fmt.Sprintf(`helpers/start.sh "%s"`, quoteShellArgument(options.StartCommand)))

	command := c.command(ctx, arguments...)
	command.Env = append(command.Env, environmentEntries(options.VariableKeys, options.Variables)...)
	command.Dir = options.FunctionDirectory
	command.Stdout = c.Stdout
	command.Stderr = c.Stderr

	if err := command.Start(); err != nil {
		return nil, err
	}

	exited := make(chan error, 1)
	go func() { exited <- command.Wait() }()

	// Whichever happens first decides. A container that dies during startup
	// must be reported as such rather than as a port timeout, which says
	// nothing about the cause.
	opened := make(chan error, 1)
	go func() { opened <- waitForPort(ctx, options.Port) }()

	select {
	case waitErr := <-exited:
		return nil, describe(waitErr,
			fmt.Sprintf("Function container exited before opening port %d.", options.Port))
	case err := <-opened:
		if err != nil {
			c.Stop(context.WithoutCancel(ctx), options.ID)

			return nil, err
		}
	}

	return func() error { return <-exited }, nil
}

// waitForPort polls until the published port accepts a connection.
//
// Readiness must stop at the transport boundary. Sending an HTTP request here
// executes the user's function before `run` has started and rejects valid
// functions that do not answer that synthetic request.
func waitForPort(ctx context.Context, port int) error {
	address := net.JoinHostPort("127.0.0.1", strconv.Itoa(port))

	var lastErr error
	for attempt := 0; attempt <= 100; attempt++ {
		select {
		case <-ctx.Done():
			return ctx.Err()
		default:
		}

		connection, err := net.DialTimeout("tcp", address, time.Second)
		if err == nil {
			connection.Close()

			return nil
		}
		lastErr = err

		time.Sleep(100 * time.Millisecond)
	}

	return fmt.Errorf("timed out waiting for port %d: %w", port, lastErr)
}

// PortAvailable reports whether a port can be published.
//
// Both loopback addresses are checked, because `localhost` is not one address: a
// server bound only to [::1]:3000 leaves 127.0.0.1:3000 free, and a browser
// resolving localhost to ::1 first reaches the other service.
//
// Dialling and binding, because neither alone is sufficient. SO_REUSEADDR lets a
// wildcard bind succeed while a specific address on the port is held, so a bind
// alone can report a port free that something is actively serving.
func PortAvailable(port int) bool {
	address := func(host string) string {
		return net.JoinHostPort(host, strconv.Itoa(port))
	}

	// Loopback refuses instantly when nothing is there, so this does not pay
	// the timeout except against a port being actively dropped.
	for _, host := range []string{"127.0.0.1", "::1"} {
		connection, err := net.DialTimeout("tcp", address(host), 100*time.Millisecond)
		if err == nil {
			connection.Close()

			return false
		}
	}

	// A host with IPv6 disabled fails this probe for a reason that is not a
	// conflict, and reading that as "in use" would reject the whole range, so
	// only an address already in use counts.
	if held(net.Listen("tcp6", address("::1"))) {
		return false
	}

	listener, err := net.Listen("tcp4", address("127.0.0.1"))
	if err != nil {
		return false
	}
	listener.Close()

	return true
}

// held reports whether a probe failed because something already has the address,
// closing the listener when it did not. `tcp6` on a host with IPv6 disabled
// fails with EAFNOSUPPORT, which says nothing about the port -- reading that as a
// conflict would reject the whole search range.
func held(listener net.Listener, err error) bool {
	if err == nil {
		listener.Close()

		return false
	}

	return errors.Is(err, syscall.EADDRINUSE)
}

// FindPort returns the first free port in [start, end).
func FindPort(start, end int) (int, bool) {
	for port := start; port < end; port++ {
		if PortAvailable(port) {
			return port, true
		}
	}

	return 0, false
}

// signalNames renders the signals a docker process realistically dies from.
// Named rather than numbered, because Go's syscall.Signal.String() gives
// "killed" where the user expects "SIGKILL".
var signalNames = map[syscall.Signal]string{
	syscall.SIGHUP:  "SIGHUP",
	syscall.SIGINT:  "SIGINT",
	syscall.SIGQUIT: "SIGQUIT",
	syscall.SIGKILL: "SIGKILL",
	syscall.SIGSEGV: "SIGSEGV",
	syscall.SIGTERM: "SIGTERM",
}

// signalName reports the signal that killed a process, or "".
//
// Interface assertions rather than a direct syscall.WaitStatus conversion:
// Windows' WaitStatus carries neither method, and this must compile and behave
// there too -- it simply reports no signal.
func signalName(state *os.ProcessState) string {
	status, ok := state.Sys().(interface{ Signaled() bool })
	if !ok || !status.Signaled() {
		return ""
	}

	signaller, ok := state.Sys().(interface{ Signal() syscall.Signal })
	if !ok {
		return "unknown"
	}

	signal := signaller.Signal()
	if name, ok := signalNames[signal]; ok {
		return name
	}

	return strconv.Itoa(int(signal))
}
