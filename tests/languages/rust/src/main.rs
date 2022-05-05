use packageName;
use std::path::PathBuf;

fn main() {
    let client = packageName::client::Client::new();

    println!("Test Started");

    let foo = packageName::services::Foo::new(&client);
    let bar = packageName::services::Bar::new(&client);
    let general = packageName::services::General::new(&client);

    // Foo

    println!("{}", foo.get("string", 123, &["string in array"]).unwrap().result);
    println!("{}", foo.post("string", 123, &["string in array"]).unwrap().result);
    println!("{}", foo.put("string", 123, &["string in array"]).unwrap().result);
    println!("{}", foo.patch("string", 123, &["string in array"]).unwrap().result);
    println!("{}", foo.delete("string", 123, &["string in array"]).unwrap().result);

    // Bar

    println!("{}", bar.get("string", 123, &["string in array"]).unwrap().result);
    println!("{}", bar.post("string", 123, &["string in array"]).unwrap().result);
    println!("{}", bar.put("string", 123, &["string in array"]).unwrap().result);
    println!("{}", bar.patch("string", 123, &["string in array"]).unwrap().result);
    println!("{}", bar.delete("string", 123, &["string in array"]).unwrap().result);

    // General
    println!("{}", general.redirect().unwrap()["result"].as_str().unwrap());
    println!("{}", general.upload("string", 123, &["string in array"], PathBuf::from("../../../resources/file.png")).unwrap().result);
    println!("{}", general.upload("string", 123, &["string in array"], PathBuf::from("../../../resources/large_file.mp4")).unwrap().result);

    match general.error400() {
      Ok(data) => println!("{}", data),
      Err(err) => println!("{}", err.message)
    }

    match general.error500() {
      Ok(data) => println!("{}", data),
      Err(err) => println!("{}", err.message)
    }

    match general.error502() {
      Ok(data) => println!("{}", data),
      Err(err) => println!("{}", err.message)
    }
}