function Await-Task {
    param (
        [Parameter(ValueFromPipeline=$true, Mandatory=$true)]
        $task
    )

    process {
        while (-not $task.AsyncWaitHandle.WaitOne(200)) { }
        $task.GetAwaiter().GetResult()
    }
}

function Print-Response {
  param (
    $response
  )
  Write-Host ($response.Content.ReadAsStringAsync().Result | ConvertFrom-Json).result
}

Add-Type -Path "/app/tests/sdks/dotnet/src/test/Appwrite.dll" | Out-Null

$client = New-Object Appwrite.Client
$foo = New-Object Appwrite.Foo -ArgumentList $client
$bar = New-Object Appwrite.Bar -ArgumentList $client
$general = New-Object Appwrite.General -ArgumentList $client

$list = $("string in array")
$response = $foo.get("string", 123, $list) | Await-Task
Print-Response $response

$response = $foo.post("string", 123, $list) | Await-Task
Print-Response $response

$response = $foo.put("string", 123, $list) | Await-Task
Print-Response $response

$response = $foo.patch("string", 123, $list) | Await-Task
Print-Response $response

$response = $foo.delete("string", 123, $list) | Await-Task
Print-Response $response

$response = $bar.get("string", 123, $list) | Await-Task
Print-Response $response

$response = $bar.post("string", 123, $list) | Await-Task
Print-Response $response

$response = $bar.put("string", 123, $list) | Await-Task
Print-Response $response

$response = $bar.patch("string", 123, $list) | Await-Task
Print-Response $response

$response = $bar.delete("string", 123, $list) | Await-Task
Print-Response $response

$response = $general.Redirect() | Await-Task
Print-Response $response
