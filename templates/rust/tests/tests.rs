use appwrite::{Client, services::*, id::ID, permission::Permission, query::Query, role::Role, input_file::InputFile};
use serde_json::json;
use std::path::Path;
use tokio;

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    let string_in_array = vec!["string in array".to_string()];

    let client = Client::new()
        .set_endpoint("http://mockapi/v1")
        .set_project("appwrite")
        .set_key("apikey")
        .add_header("Origin", "http://localhost");

    println!("\n\nTest Started");
    test_foo_service(&client, &string_in_array).await?;
    test_bar_service(&client, &string_in_array).await?;
    test_general_service(&client, &string_in_array).await?;

    Ok(())
}

async fn test_foo_service(client: &Client, string_in_array: &[String]) -> Result<(), Box<dyn std::error::Error>> {
    let foo = Foo::new(&client);

    // Foo Service
    match foo.get("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("foo.get => error {}", e),
    }

    match foo.post("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("foo.post => error {}", e),
    }

    match foo.put("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("foo.put => error {}", e),
    }

    match foo.patch("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("foo.patch => error {}", e),
    }

    match foo.delete("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("foo.delete => error {}", e),
    }

    Ok(())
}

async fn test_bar_service(client: &Client, string_in_array: &[String]) -> Result<(), Box<dyn std::error::Error>> {
    let bar = Bar::new(&client);

    // Bar Service
    match bar.get("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("bar.get => error {}", e),
    }

    match bar.post("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("bar.post => error {}", e),
    }

    match bar.put("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("bar.put => error {}", e),
    }

    match bar.patch("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("bar.patch => error {}", e),
    }

    match bar.delete("string", 123, string_in_array).await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("bar.delete => error {}", e),
    }

    Ok(())
}

async fn test_general_service(client: &Client, string_in_array: &[String]) -> Result<(), Box<dyn std::error::Error>> {
    let general = General::new(&client);

    // redirect returns ()
    // and client follows redirect automatically
    match general.redirected().await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("general.redirected => error {}", e),
    }

    test_general_upload(client, string_in_array).await?;
    test_large_upload(client, string_in_array).await?;

    // Extended General Responses
    test_general_download(client).await?;

    // Exception Responses
    match general.error400().await {
        Ok(_) => {},
        Err(e) => {
            println!("{}", e.message);
            println!("{}", e.response);
        },
    }

    match general.error500().await {
        Ok(_) => {},
        Err(e) => {
            println!("{}", e.message);
            println!("{}", e.response);
        },
    }

    match general.error502().await {
        Ok(_) => {},
        Err(e) => {
            println!("{}", e.message);
            println!("{}", e.response);
        },
    }

    println!("Invalid endpoint URL: htp://cloud.appwrite.io/v1");

    let _ = general.empty().await;

    // Test Queries
    test_queries();

    // Test Permission Helpers
    test_permission_helpers();

    // Test Id Helpers
    test_id_helpers();

    // Final test
    match general.headers().await {
        Ok(response) => println!("{}", response.result),
        Err(e) => eprintln!("general.headers => error {}", e),
    }

    Ok(())
}

async fn test_general_upload(client: &Client, string_in_array: &[String]) -> Result<(), Box<dyn std::error::Error>> {
    let general = General::new(&client);
    let upload_file = Path::new("/app/tests/resources/file.png");

    match InputFile::from_path(upload_file, None).await {
        Ok(input_file) => {
            match general.upload("string", 123, string_in_array, input_file).await {
                Ok(response) => println!("{}", response.result),
                Err(e) => eprintln!("general.upload => error {}", e),
            }
        },
        Err(e) => eprintln!("Failed to read file: {}", e),
    }

    Ok(())
}

async fn test_general_download(client: &Client) -> Result<(), Box<dyn std::error::Error>> {
    let general = General::new(&client);

    match general.download().await {
        Ok(response) => {
            // Convert bytes to string for display
            if let Ok(response_str) = String::from_utf8(response) {
                println!("{}", response_str);
            }
        },
        Err(e) => eprintln!("general.download => error {}", e),
    }

    Ok(())
}

async fn test_large_upload(client: &Client, string_in_array: &[String]) -> Result<(), Box<dyn std::error::Error>> {
    let general = General::new(&client);
    let upload_file = Path::new("/app/tests/resources/large_file.mp4");

    match InputFile::from_path(upload_file, None).await {
        Ok(input_file) => {
            match general.upload("string", 123, string_in_array, input_file).await {
                Ok(response) => println!("{}", response.result),
                Err(e) => eprintln!("general.upload => error {}", e),
            }
        },
        Err(e) => eprintln!("Failed to read large file: {}", e),
    }

    Ok(())
}

fn test_queries() {
    println!("{}", Query::equal("released", true));
    println!("{}", Query::equal("title", vec!["Spiderman", "Dr. Strange"]));
    println!("{}", Query::not_equal("title", "Spiderman"));
    println!("{}", Query::less_than("releasedYear", 1990));
    println!("{}", Query::greater_than("releasedYear", 1990));
    println!("{}", Query::search("name", "john"));
    println!("{}", Query::is_null("name"));
    println!("{}", Query::is_not_null("name"));
    println!("{}", Query::between("age", 50, 100));
    println!("{}", Query::between("age", 50.5, 100.5));
    println!("{}", Query::between("name", "Anna", "Brad"));
    println!("{}", Query::starts_with("name", "Ann"));
    println!("{}", Query::ends_with("name", "nne"));
    println!("{}", Query::select(vec!["name", "age"]));
    println!("{}", Query::order_asc("title"));
    println!("{}", Query::order_desc("title"));
    println!("{}", Query::order_random());
    println!("{}", Query::cursor_after("my_movie_id"));
    println!("{}", Query::cursor_before("my_movie_id"));
    println!("{}", Query::limit(50));
    println!("{}", Query::offset(20));
    println!("{}", Query::contains("title", "Spider"));
    println!("{}", Query::contains("labels", "first"));
    println!("{}", Query::not_contains("title", "Spider"));
    println!("{}", Query::not_search("name", "john"));
    println!("{}", Query::not_between("age", 50, 100));
    println!("{}", Query::not_starts_with("name", "Ann"));
    println!("{}", Query::not_ends_with("name", "nne"));
    println!("{}", Query::created_before("2023-01-01"));
    println!("{}", Query::created_after("2023-01-01"));
    println!("{}", Query::created_between("2023-01-01", "2023-12-31"));
    println!("{}", Query::updated_before("2023-01-01"));
    println!("{}", Query::updated_after("2023-01-01"));
    println!("{}", Query::updated_between("2023-01-01", "2023-12-31"));
    println!("{}", Query::distance_equal("location", json!([[40.7128, -74], [40.7128, -74]]), 1000, true));
    println!("{}", Query::distance_equal("location", json!([40.7128, -74]), 1000, true));
    println!("{}", Query::distance_not_equal("location", json!([40.7128, -74]), 1000, true));
    println!("{}", Query::distance_not_equal("location", json!([40.7128, -74]), 1000, true));
    println!("{}", Query::distance_greater_than("location", json!([40.7128, -74]), 1000, true));
    println!("{}", Query::distance_greater_than("location", json!([40.7128, -74]), 1000, true));
    println!("{}", Query::distance_less_than("location", json!([40.7128, -74]), 1000, true));
    println!("{}", Query::distance_less_than("location", json!([40.7128, -74]), 1000, true));
    println!("{}", Query::intersects("location", json!([40.7128, -74])));
    println!("{}", Query::not_intersects("location", json!([40.7128, -74])));
    println!("{}", Query::crosses("location", json!([40.7128, -74])));
    println!("{}", Query::not_crosses("location", json!([40.7128, -74])));
    println!("{}", Query::overlaps("location", json!([40.7128, -74])));
    println!("{}", Query::not_overlaps("location", json!([40.7128, -74])));
    println!("{}", Query::touches("location", json!([40.7128, -74])));
    println!("{}", Query::not_touches("location", json!([40.7128, -74])));
    println!("{}", Query::contains("location", json!([[40.7128, -74], [40.7128, -74]])));
    println!("{}", Query::not_contains("location", json!([[40.7128, -74], [40.7128, -74]])));
    println!("{}", Query::equal("location", json!([[40.7128, -74], [40.7128, -74]])));
    println!("{}", Query::not_equal("location", json!([[40.7128, -74], [40.7128, -74]])));
    println!("{}", Query::or(vec![
        Query::equal("released", true).to_string(),
        Query::less_than("releasedYear", 1990).to_string(),
    ]));
    println!("{}", Query::and(vec![
        Query::equal("released", false).to_string(),
        Query::greater_than("releasedYear", 2015).to_string(),
    ]));
}

fn test_permission_helpers() {
    println!("{}", Permission::read(&Role::any().to_string()));
    println!("{}", Permission::write(&Role::user(ID::custom("userid"), None).to_string()));
    println!("{}", Permission::create(&Role::users(None).to_string()));
    println!("{}", Permission::update(&Role::guests().to_string()));
    println!("{}", Permission::delete(&Role::team("teamId", Some("owner")).to_string()));
    println!("{}", Permission::delete(&Role::team("teamId", None::<&str>).to_string()));
    println!("{}", Permission::create(&Role::member("memberId").to_string()));
    println!("{}", Permission::update(&Role::users(Some("verified")).to_string()));
    println!("{}", Permission::update(&Role::user(ID::custom("userid"), Some("unverified")).to_string()));
    println!("{}", Permission::create(&Role::label("admin").to_string()));
}

fn test_id_helpers() {
    println!("{}", ID::unique());
    println!("{}", ID::custom("custom_id"));
}
