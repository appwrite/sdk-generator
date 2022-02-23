require "language/node"

class Appwrite < Formula
  desc "CLI is a Node based command-line tool for Appwrite API"
  homepage "https://appwrite.io"
  license "BSD-3-Clause"
  head "https://github.com/appwrite/sdk-for-cli.git", branch: "main"

  depends_on "node"

  def install
    system "npm", "install", *Language::Node.std_npm_install_args(libexec)
    bin.install_symlink Dir["#{libexec}/bin/*"]
  end

  test do
    system "true"
  end
end