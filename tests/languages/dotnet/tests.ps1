function Await-Task {
    param (
        [Parameter(ValueFromPipeline=$true, Mandatory=$true)]
        $task
    )

    process {
        while (-not $task.AsyncWaitHandle.WaitOne(200)) { }
        $task.GetAwaiter()
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
$response = $foo.Get("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $foo.Post("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $foo.Put("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $foo.Patch("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $foo.Delete("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $bar.Get("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $bar.Post("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $bar.Put("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $bar.Patch("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $bar.Delete("string", 123, $list) | Await-Task
Print-Response $response.GetResult()

$response = $general.Redirect() | Await-Task
Print-Response $response.GetResult()

$response = $general.Upload("string", 123, $list, (Get-Item "../../../../resources/file.png"))  | Await-Task
Print-Response $response.GetResult()

try {
    $response = $general.Error400() | Await-Task
    $response.GetResult()
} catch [Appwrite.AppwriteException] {
    Write-Host $_.Exception.Message
}

try {
    $response = $general.Error500() | Await-Task
    $response.GetResult()
} catch [Appwrite.AppwriteException] {
    Write-Host $_.Exception.Message
}