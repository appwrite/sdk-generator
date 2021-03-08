use reqwest::{
    header::{HeaderMap, HeaderValue},
    Method,
};
use std::time::Duration;
use std::{collections::HashMap, ops::Deref, string};

pub struct Client {
    endpoint: url::Url,
    headers: HeaderMap,
    client: reqwest::blocking::Client,
}

#[derive(Clone)]
pub enum ParamType {
    Bool(bool),
    Number(i64),
    String(String),
    Array(Vec<ParamType>),
    FilePath(String),
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
            client: reqwest::blocking::Client::new(),
        }
    }

    pub fn addHeader(&mut self, key: String, value: String) {
        self.headers.append(
            reqwest::header::HeaderName::from_lowercase(key.as_bytes()).unwrap(),
            (&value.to_lowercase()).parse().unwrap(),
        );
    }

    pub fn setProject(&mut self, value: String) {
        self.addHeader("X-Appwrite-Project".to_string(), value)
    }

    pub fn setKey(&mut self, value: String) {
        self.addHeader("X-Appwrite-Key".to_string(), value)
    }

    pub fn call(
        self,
        method: String,
        path: String,
        headers: Option<HashMap<String, String>>,
        params: Option<HashMap<String, ParamType>>,
    ) {
        // If we have headers in the function call we combine them with the client headers.

        let request_headers: HeaderMap = match headers {
            Some(data) => {
                let mut headers = self.headers.clone();

                for (k, v) in data {
                    headers.append(
                        reqwest::header::HeaderName::from_lowercase(k.as_bytes()).unwrap(),
                        (&v.to_lowercase()).parse().unwrap(),
                    );
                }

                headers
            }
            None => self.headers.clone(),
        };

        let content_type = match &request_headers.get("content-type") {
            Some(data) => data.to_str().unwrap(),
            None => "",
        };

        // Now start building request with reqwest
        let methodType = match method.as_str() {
            "GET" => reqwest::Method::GET,
            "POST" => reqwest::Method::POST,
            "OPTIONS" => reqwest::Method::OPTIONS,
            "PUT" => reqwest::Method::PUT,
            "DELETE" => reqwest::Method::DELETE,
            "HEAD" => reqwest::Method::HEAD,
            _ => reqwest::Method::GET,
        };

        let mut request = self.client.request(methodType, &path);

        let flattened_data = flatten(FlattenType::Normal(params.unwrap()), None);

        // First flatten the data and feed it into a FormData
        if content_type.starts_with("multipart/form-data") {
            let mut form = reqwest::blocking::multipart::Form::new();

            for (k, v) in flattened_data.clone() {
                match v {
                    ParamType::Bool(data) => {
                        form = form.text(k, data.to_string());
                    }
                    ParamType::String(data) => form = form.text(k, data),
                    ParamType::FilePath(data) => form = form.file(k, data).unwrap(),
                    ParamType::Number(data) => form = form.text(k, data.to_string()),
                    // This shouldn't be possible due to the flatten function, so we won't handle this for now
                    ParamType::Array(_data) => {
                        //todo: Feed this back into a flatten function if needed
                    }
                }
            }
            request = request.multipart(form);
        }

        if content_type.starts_with("application/json") {
          request = request.json(&queryizeData(flattened_data));
        }

        request = request.headers(request_headers);

        if method == "GET" {
          request = request.query(&queryizeData(flatten(FlattenType::Normal(params.unwrap()), None)));
        }
    }
}

enum FlattenType {
    Normal(HashMap<String, ParamType>),
    Nested(Vec<ParamType>),
}

fn queryizeData(data: Vec<(String, ParamType)>) -> Vec<(String, String)> {
    let mut output: Vec<(String, String)> = Default::default();

    for (k, v) in data {
        match v {
            ParamType::Bool(value) => output.push((k, value.to_string())),
            ParamType::String(value) => output.push((k, value)),
            ParamType::Number(value) => output.push((k, value.to_string())),
            _ => {}
        }
    }

    output
}

fn flatten(data: FlattenType, prefix: Option<String>) -> Vec<(String, ParamType)> {
    let mut output: Vec<(String, ParamType)> = Default::default();

    match data {
        FlattenType::Normal(data) => {
            for (k, v) in data {
                let final_key = match &prefix {
                    Some(current_prefix) => format!("{}[{}]", current_prefix, k),
                    None => k,
                };

                match v {
                    ParamType::Array(value) => {
                        flatten(FlattenType::Nested(value), Some(final_key)).append(&mut output);
                    }
                    value => {
                        output.push((final_key, value));
                    }
                }
            }
        }

        FlattenType::Nested(data) => {
            for (k, v) in data.iter().enumerate() {
                let final_key = match &prefix {
                    Some(current_prefix) => format!("{}[{}]", current_prefix, k),
                    None => k.to_string(),
                };

                match v {
                    ParamType::Array(value) => {
                        flatten(FlattenType::Nested(value.to_owned()), Some(final_key))
                            .append(&mut output);
                    }
                    value => {
                        output.push((final_key, value.to_owned()));
                    }
                }
            }
        }
    }

    output
}
