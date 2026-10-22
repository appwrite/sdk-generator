package typegen

import (
	"fmt"
	"strings"
)

type EnumStyle string

const (
	EnumStyleEnum  EnumStyle = "enum"
	EnumStyleConst EnumStyle = "const"
)

var EnumStyles = []EnumStyle{EnumStyleEnum, EnumStyleConst}

func EnumStyleChoices() string {
	choices := make([]string, 0, len(EnumStyles))
	for _, style := range EnumStyles {
		choices = append(choices, string(style))
	}

	return strings.Join(choices, "|")
}

func ParseEnumStyle(value string) (EnumStyle, error) {
	for _, style := range EnumStyles {
		if string(style) == value {
			return style, nil
		}
	}

	return "", fmt.Errorf("invalid --enum-style value: %s (supported: %s)", value, EnumStyleChoices())
}
