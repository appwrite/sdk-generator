use Appwrite;
use std::path::PathBuf;

fn main() {
    let client = Appwrite::client::Client::new();

    println!("Test Started");

    let foo = Appwrite::services::Foo::new(&client);
    let bar = Appwrite::services::Bar::new(&client);
    let general = Appwrite::services::General::new(&client);

    // Foo

    println!("{:?}", foo.get("string", 123, &["string in array"]).unwrap());
    println!("{:?}", foo.post("string", 123, &["string in array"]).unwrap());
    println!("{:?}", foo.put("string", 123, &["string in array"]).unwrap());
    println!("{:?}", foo.patch("string", 123, &["string in array"]).unwrap());
    println!("{:?}", foo.delete("string", 123, &["string in array"]).unwrap());

    // Bar

    println!("{:?}", bar.get("string", 123, &["string in array"]).unwrap());
    println!("{:?}", bar.post("string", 123, &["string in array"]).unwrap());
    println!("{:?}", bar.put("string", 123, &["string in array"]).unwrap());
    println!("{:?}", bar.patch("string", 123, &["string in array"]).unwrap());
    println!("{:?}", bar.delete("string", 123, &["string in array"]).unwrap());

    // General
    println!("{:?}", general.redirect().unwrap()["result"].as_str().unwrap());
    println!("{:?}", general.upload("string", 123, &["string in array"], PathBuf::from("../../../resources/file.png")).unwrap());

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