use zed_extension_api::{self as zed, Command, ContextServerId, Os, Project, Result};

const MCP_REMOTE_PACKAGE: &str = "mcp-remote";
const MCP_REMOTE_VERSION: &str = "0.1.38";
const APPWRITE_MCP_URL: &str = "https://mcp.appwrite.io/";

struct AppwriteMcpExtension;

impl zed::Extension for AppwriteMcpExtension {
    fn new() -> Self {
        Self
    }

    fn context_server_command(
        &mut self,
        _context_server_id: &ContextServerId,
        _project: &Project,
    ) -> Result<Command> {
        let installed_version = zed::npm_package_installed_version(MCP_REMOTE_PACKAGE)?;
        if installed_version.as_deref() != Some(MCP_REMOTE_VERSION) {
            zed::npm_install_package(MCP_REMOTE_PACKAGE, MCP_REMOTE_VERSION)?;
        }

        let (os, _) = zed::current_platform();
        let command = if os == Os::Windows {
            "node_modules/.bin/mcp-remote.cmd"
        } else {
            let command = "node_modules/.bin/mcp-remote";
            zed::make_file_executable(command)?;
            command
        };

        Ok(Command {
            command: command.to_string(),
            args: vec![APPWRITE_MCP_URL.to_string()],
            env: Vec::new(),
        })
    }
}

zed::register_extension!(AppwriteMcpExtension);
