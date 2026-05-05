use serde::Serialize;
use serde_json::Value;

#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum Condition {
    Equal,
    NotEqual,
    GreaterThan,
    GreaterThanEqual,
    LessThan,
    LessThanEqual,
    Contains,
    IsNull,
    IsNotNull,
}

impl Condition {
    pub fn as_str(&self) -> &'static str {
        match self {
            Condition::Equal => "equal",
            Condition::NotEqual => "notEqual",
            Condition::GreaterThan => "greaterThan",
            Condition::GreaterThanEqual => "greaterThanEqual",
            Condition::LessThan => "lessThan",
            Condition::LessThanEqual => "lessThanEqual",
            Condition::Contains => "contains",
            Condition::IsNull => "isNull",
            Condition::IsNotNull => "isNotNull",
        }
    }
}

#[derive(Debug, Serialize)]
struct OperatorData {
    method: String,
    values: Vec<Value>,
}

fn parse_operator(method: &str, values: Vec<Value>) -> String {
    let data = OperatorData {
        method: method.to_string(),
        values,
    };
    serde_json::to_string(&data).expect("Failed to serialize operator data")
}

fn validate_numeric(value: &Value, param_name: &str) {
     if !value.is_number() {
        panic!("{} must be a number", param_name);
    }
    if let Some(num) = value.as_f64() {
        if num.is_nan() || num.is_infinite() {
            panic!("{} cannot be NaN or Infinity", param_name);
        }
    }
}

fn validate_nonzero_divisor(value: &Value, param_name: &str) {
    validate_numeric(value, param_name);

    if let Some(num) = value.as_f64() {
        if num == 0.0 {
            panic!("{} cannot be zero", param_name);
        }
    } else if let Some(num) = value.as_i64() {
        if num == 0 {
            panic!("{} cannot be zero", param_name);
        }
    }
}

pub fn increment() -> String {
    parse_operator("increment", vec![Value::from(1)])
}

pub fn increment_by<T: Into<Value>>(value: T) -> String {
    let val = value.into();
    validate_numeric(&val, "value");
    parse_operator("increment", vec![val])
}

pub fn increment_with_max<T: Into<Value>, M: Into<Value>>(value: T, max: M) -> String {
    let val = value.into();
    let max_val = max.into();
    validate_numeric(&val, "value");
    validate_numeric(&max_val, "max");
    parse_operator("increment", vec![val, max_val])
}

pub fn decrement() -> String {
    parse_operator("decrement", vec![Value::from(1)])
}

pub fn decrement_by<T: Into<Value>>(value: T) -> String {
    let val = value.into();
    validate_numeric(&val, "value");
    parse_operator("decrement", vec![val])
}

pub fn decrement_with_min<T: Into<Value>, M: Into<Value>>(value: T, min: M) -> String {
    let val = value.into();
    let min_val = min.into();
    validate_numeric(&val, "value");
    validate_numeric(&min_val, "min");
    parse_operator("decrement", vec![val, min_val])
}

pub fn multiply<T: Into<Value>>(factor: T) -> String {
    let val = factor.into();
    validate_numeric(&val, "factor");
    parse_operator("multiply", vec![val])
}

pub fn multiply_with_max<T: Into<Value>, M: Into<Value>>(factor: T, max: M) -> String {
    let val = factor.into();
    let max_val = max.into();
    validate_numeric(&val, "factor");
    validate_numeric(&max_val, "max");
    parse_operator("multiply", vec![val, max_val])
}

pub fn divide<T: Into<Value>>(divisor: T) -> String {
    let val = divisor.into();
    validate_nonzero_divisor(&val, "divisor");
    parse_operator("divide", vec![val])
}

pub fn divide_with_min<T: Into<Value>, M: Into<Value>>(divisor: T, min: M) -> String {
    let val = divisor.into();
    let min_val = min.into();
    validate_nonzero_divisor(&val, "divisor");
    validate_numeric(&min_val, "min");
    parse_operator("divide", vec![val, min_val])
}

pub fn modulo<T: Into<Value>>(divisor: T) -> String {
    let val = divisor.into();
    validate_nonzero_divisor(&val, "divisor");
    parse_operator("modulo", vec![val])
}

pub fn power<T: Into<Value>>(exponent: T) -> String {
    let val = exponent.into();
    validate_numeric(&val, "exponent");
    parse_operator("power", vec![val])
}

pub fn power_with_max<T: Into<Value>, M: Into<Value>>(exponent: T, max: M) -> String {
    let val = exponent.into();
    let max_val = max.into();
    validate_numeric(&val, "exponent");
    validate_numeric(&max_val, "max");
    parse_operator("power", vec![val, max_val])
}

pub fn array_append<T: Serialize>(values: &[T]) -> String {
    let vals: Vec<Value> = values
        .iter()
        .map(|v| serde_json::to_value(v).expect("Failed to serialize value"))
        .collect();
    parse_operator("arrayAppend", vals)
}

pub fn array_prepend<T: Serialize>(values: &[T]) -> String {
    let vals: Vec<Value> = values
        .iter()
        .map(|v| serde_json::to_value(v).expect("Failed to serialize value"))
        .collect();
    parse_operator("arrayPrepend", vals)
}

pub fn array_insert<T: Into<Value>>(index: i64, value: T) -> String {
    parse_operator("arrayInsert", vec![Value::from(index), value.into()])
}

pub fn array_remove<T: Into<Value>>(value: T) -> String {
    parse_operator("arrayRemove", vec![value.into()])
}

pub fn array_unique() -> String {
    parse_operator("arrayUnique", vec![])
}

pub fn array_intersect<T: Serialize>(values: &[T]) -> String {
    let vals: Vec<Value> = values
        .iter()
        .map(|v| serde_json::to_value(v).expect("Failed to serialize value"))
        .collect();
    parse_operator("arrayIntersect", vals)
}

pub fn array_diff<T: Serialize>(values: &[T]) -> String {
    let vals: Vec<Value> = values
        .iter()
        .map(|v| serde_json::to_value(v).expect("Failed to serialize value"))
        .collect();
    parse_operator("arrayDiff", vals)
}

pub fn array_filter(condition: Condition) -> String {
    parse_operator(
        "arrayFilter",
        vec![Value::from(condition.as_str()), Value::Null],
    )
}

pub fn array_filter_with_value<T: Into<Value>>(condition: Condition, value: T) -> String {
    parse_operator(
        "arrayFilter",
        vec![Value::from(condition.as_str()), value.into()],
    )
}

pub fn string_concat<S: Into<String>>(value: S) -> String {
    parse_operator("stringConcat", vec![Value::from(value.into())])
}

pub fn string_replace<S: Into<String>>(search: S, replace: S) -> String {
    parse_operator(
        "stringReplace",
        vec![Value::from(search.into()), Value::from(replace.into())],
    )
}

pub fn toggle() -> String {
    parse_operator("toggle", vec![])
}

pub fn date_add_days(days: i64) -> String {
    parse_operator("dateAddDays", vec![Value::from(days)])
}

pub fn date_sub_days(days: i64) -> String {
    parse_operator("dateSubDays", vec![Value::from(days)])
}

pub fn date_set_now() -> String {
    parse_operator("dateSetNow", vec![])
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_increment() {
        assert_eq!(increment(), r#"{"method":"increment","values":[1]}"#);
    }

    #[test]
    fn test_increment_by() {
        assert_eq!(increment_by(5), r#"{"method":"increment","values":[5]}"#);
    }

    #[test]
    fn test_increment_with_max() {
        assert_eq!(increment_with_max(5, 100), r#"{"method":"increment","values":[5,100]}"#);
    }

    #[test]
    fn test_decrement() {
        assert_eq!(decrement(), r#"{"method":"decrement","values":[1]}"#);
    }

    #[test]
    fn test_decrement_by() {
        assert_eq!(decrement_by(3), r#"{"method":"decrement","values":[3]}"#);
    }

    #[test]
    fn test_decrement_with_min() {
        assert_eq!(decrement_with_min(3, 0), r#"{"method":"decrement","values":[3,0]}"#);
    }

    #[test]
    fn test_multiply() {
        assert_eq!(multiply(2), r#"{"method":"multiply","values":[2]}"#);
    }

    #[test]
    fn test_multiply_with_max() {
        assert_eq!(multiply_with_max(3, 1000), r#"{"method":"multiply","values":[3,1000]}"#);
    }

    #[test]
    fn test_divide() {
        assert_eq!(divide(2), r#"{"method":"divide","values":[2]}"#);
    }

    #[test]
    fn test_divide_with_min() {
        assert_eq!(divide_with_min(4, 1), r#"{"method":"divide","values":[4,1]}"#);
    }

    #[test]
    fn test_modulo() {
        assert_eq!(modulo(5), r#"{"method":"modulo","values":[5]}"#);
    }

    #[test]
    fn test_power() {
        assert_eq!(power(2), r#"{"method":"power","values":[2]}"#);
    }

    #[test]
    fn test_power_with_max() {
        assert_eq!(power_with_max(3, 100), r#"{"method":"power","values":[3,100]}"#);
    }

    #[test]
    fn test_array_append() {
        assert_eq!(array_append(&["item1", "item2"]), r#"{"method":"arrayAppend","values":["item1","item2"]}"#);
    }

    #[test]
    fn test_array_prepend() {
        assert_eq!(array_prepend(&["first", "second"]), r#"{"method":"arrayPrepend","values":["first","second"]}"#);
    }

    #[test]
    fn test_array_insert() {
        assert_eq!(array_insert(0, "newItem"), r#"{"method":"arrayInsert","values":[0,"newItem"]}"#);
    }

    #[test]
    fn test_array_remove() {
        assert_eq!(array_remove("oldItem"), r#"{"method":"arrayRemove","values":["oldItem"]}"#);
    }

    #[test]
    fn test_array_unique() {
        assert_eq!(array_unique(), r#"{"method":"arrayUnique","values":[]}"#);
    }

    #[test]
    fn test_array_intersect() {
        assert_eq!(array_intersect(&["a", "b", "c"]), r#"{"method":"arrayIntersect","values":["a","b","c"]}"#);
    }

    #[test]
    fn test_array_diff() {
        assert_eq!(array_diff(&["x", "y"]), r#"{"method":"arrayDiff","values":["x","y"]}"#);
    }

    #[test]
    fn test_array_filter() {
        assert_eq!(array_filter(Condition::Equal), r#"{"method":"arrayFilter","values":["equal",null]}"#);
    }

    #[test]
    fn test_array_filter_with_value() {
        assert_eq!(array_filter_with_value(Condition::Equal, "test"), r#"{"method":"arrayFilter","values":["equal","test"]}"#);
    }

    #[test]
    fn test_string_concat() {
        assert_eq!(string_concat("suffix"), r#"{"method":"stringConcat","values":["suffix"]}"#);
    }

    #[test]
    fn test_string_replace() {
        assert_eq!(string_replace("old", "new"), r#"{"method":"stringReplace","values":["old","new"]}"#);
    }

    #[test]
    fn test_toggle() {
        assert_eq!(toggle(), r#"{"method":"toggle","values":[]}"#);
    }

    #[test]
    fn test_date_add_days() {
        assert_eq!(date_add_days(7), r#"{"method":"dateAddDays","values":[7]}"#);
    }

    #[test]
    fn test_date_sub_days() {
        assert_eq!(date_sub_days(3), r#"{"method":"dateSubDays","values":[3]}"#);
    }

    #[test]
    fn test_date_set_now() {
        assert_eq!(date_set_now(), r#"{"method":"dateSetNow","values":[]}"#);
    }

    #[test]
    #[should_panic(expected = "divisor cannot be zero")]
    fn test_divide_zero() {
        divide(0);
    }

    #[test]
    #[should_panic(expected = "divisor cannot be zero")]
    fn test_modulo_zero() {
        modulo(0);
    }
}
