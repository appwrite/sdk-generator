use std::{collections::HashMap, ops::Deref, string};
use reqwest::header::{HeaderMap, HeaderValue};
use ureq::{Agent, Request};
use std::time::Duration;

pub struct Client {
  endpoint: url::Url,
  headers: HeaderMap,
  client: reqwest::Client
}

#[derive(Clone)]
pub enum ParamType {
  Bool(bool),
  Number(i64),
  String(String),
  Array(Vec<ParamType>),
  File(Vec<u8>)
}

fn set_headers(request: Request, headers: HashMap<String, String>) -> Request {
  for (k, v) in headers.iter() {
    let request = request.clone().set(&k, &v); // The clone is due to ownership issues.
  };

  request
}

// fn set_params(request: Request, params: HashMap<String, ParamType>) -> Request {
//   for (k, v) in params.iter() {
//     match v {
//       ParamType::String(data) => {
//         let request = request.clone().query(&k, data); // The clone is due to ownership issues.
//       },
//       ParamType::Number(data) => {
//         let request = request.clone
//       }
//       _ => {}
//     }
//   };
// 
//   request
// }

impl Client {
  pub fn new() -> Self {
    Self {
      endpoint: "https://appwrite.io/v1".parse().unwrap(),
      headers: HeaderMap::new(),
      client: reqwest::Client::new()
    }
  }

  pub fn addHeader(&mut self, key: String, value: String) {
    self.headers.append(reqwest::header::HeaderName::from_lowercase(key.as_bytes()).unwrap(), (&value.to_lowercase()).parse().unwrap());
  }

  pub fn setProject(&mut self, value: String) {
    self.addHeader("X-Appwrite-Project".to_string(), value)
  }

  pub fn setKey(&mut self, value: String) {
    self.addHeader("X-Appwrite-Key".to_string(), value)
  }

  pub fn call(self, method: String, path: String, headers: Option<HashMap<String, String>>, params: Option<HashMap<String, ParamType>>) {
    // If we have headers in the function call we combine them with the client headers.

    let request_headers: HeaderMap = match headers {
      Some(data) => {
        let mut headers = self.headers.clone();
      
        for (k, v) in data {
          headers.append(reqwest::header::HeaderName::from_lowercase(k.as_bytes()).unwrap(), (&v.to_lowercase()).parse().unwrap());
        }

        headers
      },
      None => {
        self.headers.clone()
      }
    };

    let content_type = match &request_headers.get("content-type") {
      Some(data) => data.to_str().unwrap(),
      None => ""
    };

    // Create FormData

    // First flatten the data
    if (content_type.starts_with("multipart/form-data")) {
      let mut form = reqwest::blocking::multipart::Form::new();

      let flattened_data = flatten(FlattenType::Normal(params.unwrap()), None);

      for (k, v) in flattened_data {
        match v {
          ParamType::Bool(data) => {
            form = form.text(k, data.to_string());
          },
          ParamType::String(data) => {
            form = form.text(k, data)
          },
          ParamType::File(data) => {
            form = form.file
          }
        }
      }

    }

    

    // Now start building request with reqwest
    let methodType = match method.as_str() {
      "GET" => reqwest::Method::GET,
      "POST" => reqwest::Method::POST,
      "OPTIONS" => reqwest::Method::OPTIONS,
      "PUT" => reqwest::Method::PUT,
      "DELETE" => reqwest::Method::DELETE,
      "HEAD" => reqwest::Method::HEAD,
      _ => reqwest::Method::GET
    };

    let mut request = self.client.request(methodType, &path);

    request.headers(request_headers);
  }
}

enum FlattenType {
  Normal(HashMap<String, ParamType>),
  Nested(Vec<ParamType>)
}

fn flatten(data: FlattenType, prefix: Option<String>) -> Vec<(String, ParamType)> {
  let mut output: Vec<(String, ParamType)> = Default::default();

  match data {
    FlattenType::Normal(data) => {
      for (k, v) in data {
        let final_key = match &prefix {
          Some(current_prefix) => format!("{}[{}]", current_prefix, k),
          None => k
        };
    
        match v {
          ParamType::Array(value) => {
            flatten(FlattenType::Nested(value), Some(final_key)).append(&mut output);
          },
          value => {
            output.push((final_key, value));
          }
        }
      }
    },

    FlattenType::Nested(data) => {
      for (k, v) in data.iter().enumerate() {
        let final_key = match &prefix {
          Some(current_prefix) => format!("{}[{}]", current_prefix, k),
          None => k.to_string()
        };
    
        match v {
          ParamType::Array(value) => {
            flatten(FlattenType::Nested(value.to_owned()), Some(final_key)).append(&mut output);
          },
          value => {
            output.push((final_key, value.to_owned()));
          }
        }
      }
    }
  }

  output
}