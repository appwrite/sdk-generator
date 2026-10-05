// Package topic builds MQTT topics for Push, level by level.
//
//	topic.Path([]string{"user", userID, "notification"}).String() // "user/123/notification"
//	topic.Path([]string{"user"}).Any().Path([]string{"notification"}).String() // "user/+/notification"
//	topic.Path([]string{"org"}).Any().Path([]string{"logs"}).All().String() // "org/+/logs/#"
//
// Any matches exactly one level and All everything below, at any depth (including the
// parent itself). All ends the topic, so it returns a ResolvedTopic, which has no methods
// to append further levels.
//
// Path panics when given invalid levels (no levels, an empty level, or a level holding
// "/", "+" or "#"). Invalid levels are a programmer error, like a bad pattern passed to
// regexp.MustCompile.
package topic

import (
	"fmt"
	"strings"
)

// Topic is a topic being built. Create one with Path or Any.
type Topic struct {
	levels []string
}

// ResolvedTopic is a topic ended by All: nothing can be appended after the "#" wildcard.
type ResolvedTopic struct {
	levels []string
}

// Path starts a topic with the given levels, e.g. topic.Path([]string{"user", userID}).
// It panics if the levels are invalid.
func Path(levels []string) Topic {
	return Topic{levels: levelsOf(levels)}
}

// Any starts a topic with a single-level wildcard ("+").
func Any() Topic {
	return Topic{levels: []string{"+"}}
}

// All returns the multi-level wildcard alone ("#"): it matches every topic.
func All() ResolvedTopic {
	return ResolvedTopic{levels: []string{"#"}}
}

// Path appends the given levels. It panics if the levels are invalid.
func (t Topic) Path(levels []string) Topic {
	return Topic{levels: appendLevels(t.levels, levelsOf(levels)...)}
}

// Any appends a single-level wildcard ("+"): it matches exactly one level.
func (t Topic) Any() Topic {
	return Topic{levels: appendLevels(t.levels, "+")}
}

// All appends the multi-level wildcard ("#") and ends the topic.
func (t Topic) All() ResolvedTopic {
	return ResolvedTopic{levels: appendLevels(t.levels, "#")}
}

// String joins the levels with "/".
func (t Topic) String() string {
	return strings.Join(t.levels, "/")
}

// String joins the levels with "/".
func (t ResolvedTopic) String() string {
	return strings.Join(t.levels, "/")
}

// appendLevels copies the levels so topics built from a shared prefix never alias.
func appendLevels(levels []string, more ...string) []string {
	result := make([]string, 0, len(levels)+len(more))
	result = append(result, levels...)
	return append(result, more...)
}

// levelsOf validates the levels passed to Path: a non-empty list of non-empty strings, none
// holding the level separator "/" or the wildcards "+" and "#" (use Any and All for those).
func levelsOf(levels []string) []string {
	if len(levels) == 0 {
		panic("path() needs at least one level")
	}
	for index, level := range levels {
		if level == "" {
			panic(fmt.Sprintf("empty level at index %d", index))
		}
		if strings.Contains(level, "/") {
			parts := strings.Split(level, "/")
			for i, part := range parts {
				parts[i] = "'" + part + "'"
			}
			panic(fmt.Sprintf("\"%s\" contains \"/\", split it: path([%s])", level, strings.Join(parts, ", ")))
		}
		if level == "+" {
			panic("\"+\" is reserved, use any()")
		}
		if strings.Contains(level, "+") {
			panic(fmt.Sprintf("\"%s\" contains \"+\", use any() for wildcards", level))
		}
		if level == "#" {
			panic("\"#\" is reserved, use all()")
		}
		if strings.Contains(level, "#") {
			panic(fmt.Sprintf("\"%s\" contains \"#\", use all() for wildcards", level))
		}
	}
	return append([]string(nil), levels...)
}
