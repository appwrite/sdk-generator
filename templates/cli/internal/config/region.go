package config

import (
	"regexp"
	"strings"
)

// SuggestRegion picks the Cloud region closest to the user, the same way the
// console suggests one for a new project.
//
// Country decides: a region in the user's country wins, and a US user never
// lands in Toronto nor a Canadian one in New York. The time zone only splits
// the US between New York and San Francisco, Mexico between its coasts, and
// stands in when country and continent are unknown. Returns "" only when
// available is empty.
func SuggestRegion(country, continent, timeZone string, available []string) string {
	regions := map[string]bool{}
	for _, region := range available {
		if region = strings.ToLower(strings.TrimSpace(region)); region != "" {
			regions[region] = true
		}
	}
	if len(regions) == 0 {
		return ""
	}

	country = normalizeCountry(country)
	continent = strings.ToUpper(strings.TrimSpace(continent))
	timeZone = strings.TrimSpace(timeZone)

	var ranked []string
	if country != "" {
		ranked = countryRegions(country, timeZone)
	}
	if ranked == nil && len(continent) == 2 {
		ranked = continentRegions[continent]
	}
	if ranked == nil && timeZone != "" {
		ranked = timeZoneRegions(timeZone)
	}

	for _, preferences := range [][]string{ranked, liveRegions} {
		for _, region := range preferences {
			if regions[region] {
				return region
			}
		}
	}
	for _, region := range available {
		if region = strings.ToLower(strings.TrimSpace(region)); region != "" {
			return region
		}
	}

	return ""
}

var countryCode = regexp.MustCompile(`^[A-Z]{2}$`)

// normalizeCountry drops the unknown markers geo lookups answer with: `--`
// from Appwrite, `XX` and `T1` from CDNs.
func normalizeCountry(country string) string {
	country = strings.ToUpper(strings.TrimSpace(country))
	if !countryCode.MatchString(country) || country == "XX" || country == "T1" {
		return ""
	}

	return country
}

func codes(groups ...string) map[string]bool {
	set := map[string]bool{}
	for _, group := range groups {
		for _, code := range strings.Fields(group) {
			set[code] = true
		}
	}

	return set
}

var liveRegions = []string{"fra", "nyc", "sfo", "sgp", "syd", "tor"}

var (
	fraRegions = []string{"fra", "nyc", "sgp", "syd", "tor", "sfo"}
	nycRegions = []string{"nyc", "tor", "sfo", "fra", "sgp", "syd"}
	sfoRegions = []string{"sfo", "nyc", "tor", "sgp", "syd", "fra"}
	sgpRegions = []string{"sgp", "syd", "fra", "nyc", "tor", "sfo"}
	sydRegions = []string{"syd", "sgp", "sfo", "fra", "nyc", "tor"}
	torRegions = []string{"tor", "nyc", "sfo", "fra", "sgp", "syd"}
)

// Europe, Africa, the Middle East and Central Asia.
var fraCountries = codes(
	"AD AL AT AX BA BE BG BY CH CY CZ DE DK EE ES FI FO FR GB GG GI GR HR HU IE IM IS IT JE LI LT LU LV MC MD ME MK MT NL NO PL PT RO RS SE SI SK SM UA VA XK",
	"RU",
	"AO BF BI BJ BW CD CF CG CI CM CV DJ DZ EG EH ER ET GA GH GM GN GQ GW KE KM LR LS LY MA MG ML MR MU MW MZ NA NE NG RE RW SC SD SH SL SN SO SS ST SZ TD TG TN TZ UG YT ZA ZM ZW",
	"AE AM AZ BH GE IL IQ JO KW LB OM PS QA SA SY TR YE",
	"KG KZ TJ TM UZ",
	"AF IR",
)

// South, South-East and East Asia.
var sgpCountries = codes(
	"BD BT IN LK MV NP PK",
	"BN KH ID LA MM MY PH SG TH TL VN",
	"CN HK JP KP KR MO MN TW",
	"GU MP",
)

// Australia, New Zealand and the Pacific.
var sydCountries = codes(
	"AU CC CX HM NF NZ",
	"FJ FM KI MH NC NR PG PW SB VU",
	"AS CK NU PF PN TK TO TV WF WS",
)

// The Caribbean, Central and South America. The US and Mexico are split by
// time zone instead, and Canada has its own region.
var nycCountries = codes(
	"AG AI AR AW BB BL BM BO BQ BR BS BZ CL CO CR CU CW DM DO EC FK GD GF GL GP GT GY HN HT JM KN KY LC MF MQ MS NI PA PE PM PR PY SR SV SX TC TT UY VC VE VG VI",
)

// A local region first, for countries Cloud has one in or plans to.
var countryPreferences = map[string][]string{
	"CA": {"tor", "nyc", "sfo", "fra", "sgp", "syd"},
	"GB": {"lon", "fra", "nyc", "sgp", "syd", "tor"},
	"IE": {"lon", "fra", "nyc", "sgp", "syd", "tor"},
	"IM": {"lon", "fra", "nyc", "sgp", "syd", "tor"},
	"GG": {"lon", "fra", "nyc", "sgp", "syd", "tor"},
	"JE": {"lon", "fra", "nyc", "sgp", "syd", "tor"},
	"NL": {"ams", "fra", "nyc", "sgp", "syd", "tor"},
	"BE": {"ams", "fra", "nyc", "sgp", "syd", "tor"},
	"LU": {"ams", "fra", "nyc", "sgp", "syd", "tor"},
	"IN": {"blr", "sgp", "fra", "syd", "nyc", "tor"},
	"NP": {"blr", "sgp", "fra", "syd", "nyc", "tor"},
	"BD": {"blr", "sgp", "fra", "syd", "nyc", "tor"},
	"LK": {"blr", "sgp", "fra", "syd", "nyc", "tor"},
	"MV": {"blr", "sgp", "fra", "syd", "nyc", "tor"},
	"BT": {"blr", "sgp", "fra", "syd", "nyc", "tor"},
	"PK": {"blr", "sgp", "fra", "syd", "nyc", "tor"},
	"SG": {"sgp", "syd", "fra", "nyc", "sfo", "tor"},
	"AU": {"syd", "sgp", "sfo", "fra", "nyc", "tor"},
	"DE": {"fra", "nyc", "sgp", "syd", "tor"},
}

var continentRegions = map[string][]string{
	"EU": {"fra", "lon", "ams", "nyc", "sgp", "syd", "tor"},
	"AF": {"fra", "sgp", "nyc", "syd", "tor"},
	"AS": {"sgp", "blr", "fra", "syd", "nyc", "tor"},
	"NA": {"nyc", "tor", "sfo", "fra", "sgp", "syd"},
	"SA": {"nyc", "sfo", "tor", "fra", "sgp", "syd"},
	"OC": {"syd", "sgp", "sfo", "fra", "nyc", "tor"},
	"AN": {"syd", "fra", "sfo", "nyc", "sgp", "tor"},
}

// Pacific, Mountain, Alaska and Hawaii: the US west of New York's reach.
var westernAmericaTimeZones = codes(
	"America/Adak America/Anchorage America/Boise America/Cambridge_Bay America/Chihuahua America/Ciudad_Juarez",
	"America/Creston America/Dawson America/Dawson_Creek America/Denver America/Edmonton America/Fort_Nelson",
	"America/Hermosillo America/Inuvik America/Juneau America/Los_Angeles America/Mazatlan America/Metlakatla",
	"America/Nome America/Ojinaga America/Phoenix America/Shiprock America/Sitka America/Tijuana America/Vancouver",
	"America/Whitehorse America/Yakutat America/Yellowknife MST7MDT Navajo PST8PDT",
	"Pacific/Honolulu Pacific/Johnston Pacific/Midway Pacific/Wake",
	"US/Alaska US/Aleutian US/Arizona US/Hawaii US/Mountain US/Pacific US/Pacific-New",
)

var easternMexicoTimeZones = codes("America/Cancun America/Matamoros")

var torontoTimeZones = codes(
	"America/Atikokan America/Blanc-Sablon America/Coral_Harbour America/Glace_Bay America/Goose_Bay America/Halifax",
	"America/Iqaluit America/Moncton America/Montreal America/Nipigon America/Pangnirtung America/Rainy_River",
	"America/Rankin_Inlet America/Regina America/Resolute America/St_Johns America/Swift_Current America/Thunder_Bay",
	"America/Toronto America/Winnipeg",
	"Canada/Atlantic Canada/Central Canada/Eastern Canada/Newfoundland Canada/Saskatchewan",
)

var middleEastTimeZones = codes(
	"Asia/Amman Asia/Baghdad Asia/Bahrain Asia/Baku Asia/Beirut Asia/Damascus Asia/Dubai Asia/Famagusta Asia/Gaza",
	"Asia/Hebron Asia/Istanbul Asia/Jerusalem Asia/Kuwait Asia/Muscat Asia/Nicosia Asia/Qatar Asia/Riyadh",
	"Asia/Tehran Asia/Tbilisi Asia/Yerevan Europe/Istanbul",
)

var oceaniaTimeZones = []string{
	"Pacific/Fiji", "Pacific/Port_Moresby", "Pacific/Guadalcanal", "Pacific/Noumea", "Pacific/Tongatapu", "Pacific/Apia",
}

func countryRegions(country, timeZone string) []string {
	switch {
	case country == "US" && westernAmericaTimeZones[timeZone]:
		return sfoRegions
	case country == "US":
		return nycRegions
	case country == "MX" && easternMexicoTimeZones[timeZone]:
		return nycRegions
	case country == "MX":
		return sfoRegions
	case countryPreferences[country] != nil:
		return countryPreferences[country]
	case fraCountries[country]:
		return fraRegions
	case sgpCountries[country]:
		return sgpRegions
	case sydCountries[country]:
		return sydRegions
	case nycCountries[country]:
		return nycRegions
	}

	return nil
}

func timeZoneRegions(timeZone string) []string {
	hasPrefix := func(prefixes ...string) bool {
		for _, prefix := range prefixes {
			if strings.HasPrefix(timeZone, prefix) {
				return true
			}
		}
		return false
	}

	switch {
	case westernAmericaTimeZones[timeZone]:
		return sfoRegions
	case torontoTimeZones[timeZone]:
		return torRegions
	case hasPrefix("America/", "US/"):
		return nycRegions
	case hasPrefix("Canada/"):
		return torRegions
	case middleEastTimeZones[timeZone], hasPrefix("Europe/", "Africa/"):
		return fraRegions
	case hasPrefix("Australia/") || timeZone == "Pacific/Auckland" || hasPrefix(oceaniaTimeZones...):
		return sydRegions
	case hasPrefix("Asia/", "Pacific/"):
		return sgpRegions
	}

	return nil
}
