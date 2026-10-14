# Third-party runtime plugins

The DLLs in this directory are copied from NuGet packages published by Microsoft
and are included so the Unity SDK can use `System.Text.Json` on Unity 2021.

To update these files, download the same package names from NuGet, copy the
`lib/netstandard2.0/*.dll` files into this directory, and update the table below
with the new package versions and SHA-256 checksums.

`dependencies.lock.json` contains the same package metadata in a structured
format for automated dependency and CVE audits.

| File | NuGet package | Version | SHA-256 |
| --- | --- | --- | --- |
| `Microsoft.Bcl.AsyncInterfaces.dll` | `Microsoft.Bcl.AsyncInterfaces` | `9.0.6` | `eb507b48362b3b0599ad7f236da1bfba9da386c37301493dbfb725d5116e5842` |
| `System.IO.Pipelines.dll` | `System.IO.Pipelines` | `9.0.6` | `ca8ecbb502543c635fd53bfb41c6b6e68cd2ecad5e4464f2492f78edc3192d3b` |
| `System.Runtime.CompilerServices.Unsafe.dll` | `System.Runtime.CompilerServices.Unsafe` | `6.0.0` | `01748200f2400c742aa689f1f5101bd6298efdfd92c00c18f4fa473847235ba9` |
| `System.Text.Encodings.Web.dll` | `System.Text.Encodings.Web` | `9.0.6` | `7089712df9f3f07862317652cbcca613c15fee759209f9b9d0c624dc81538829` |
| `System.Text.Json.dll` | `System.Text.Json` | `9.0.6` | `bf1f8a674d9bb26bc7af4cefc2936bf4bda637dc9d3b71d6ca77c0b6a254fa83` |

These Microsoft packages are licensed under the MIT license. See each package's
NuGet metadata for full license and repository details.
