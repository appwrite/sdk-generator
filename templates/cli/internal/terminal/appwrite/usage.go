package appwrite

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"net/url"
	"slices"
	"strconv"
	"time"
)

// UsageKind is which of the usage routes a metric is read from: events, what was counted in a
// bucket of time, as requests or reads, or gauges, a level sampled when it changes, as storage.
type UsageKind string

// The kinds of usage.
const (
	UsageEvents UsageKind = "events"
	UsageGauges UsageKind = "gauges"
)

// UsageQuery is a read of the usage of a project, which Appwrite 2.7 keeps in clickhouse and
// answers at /v1/usage/events and /v1/usage/gauges, with a key's usage.read scope or a console
// login: some metrics of a kind, in buckets of an interval from start to end, of every resource
// or of one, as the reads of a database.
type UsageQuery struct {
	Kind     UsageKind
	Metrics  []string
	Interval time.Duration
	Start    time.Time
	End      time.Time
	// ResourceType and ResourceID narrow the usage to one resource, or one kind of them, as
	// project for the project's own gauges.
	ResourceType string
	ResourceID   string
	// Path narrows events to the requests to a route and the routes below it, as the requests to a
	// table and to its rows.
	Path string
	// ByResource breaks the points down by the resource they are of, so a list reads the usage of
	// each of its resources at once.
	ByResource bool
	// Dimensions break the points down by more of what a request was, as its service, path or
	// method, which each point then carries in Dims.
	Dimensions []string
	// Exclude leaves out the points of the services named, as the health checks.
	Exclude []string
	// Top orders the points by their value, the highest first, and keeps that many.
	Top int
}

// Point is the value of a metric in a bucket of time, or over the window when the query has no
// interval, of the resource it is of when the query was broken down by resource, and of what else
// it was broken down by.
type Point struct {
	Time       time.Time
	Value      float64
	ResourceID string
	Dims       map[string]string
}

// intervals are the bucket sizes the usage routes take, by their names.
var intervals = map[time.Duration]string{
	time.Minute: "1m", 15 * time.Minute: "15m", 30 * time.Minute: "30m", time.Hour: "1h", 24 * time.Hour: "1d",
}

// Usage reads the points of the metrics of a query, by their metric, oldest first. A bucket in
// which nothing was counted or sampled has no point. A path is read as two queries, the route
// itself and the routes below it, so the requests to /v1/users/u1 leave out those to
// /v1/users/u10.
func (c *Client) Usage(ctx context.Context, q UsageQuery) (map[string][]Point, error) {
	if q.Path == "" {
		return c.usage(ctx, q, nil)
	}
	same := equal("path", q.Path)
	out, err := c.usage(ctx, q, &same)
	if err != nil {
		return nil, err
	}
	below, err := c.usage(ctx, q, &query{Method: "startsWith", Attribute: "path", Values: []any{q.Path + "/"}})
	if err != nil {
		return nil, err
	}
	for metric, points := range below {
		out[metric] = append(out[metric], points...)
		slices.SortStableFunc(out[metric], func(a, b Point) int { return a.Time.Compare(b.Time) })
	}
	return out, nil
}

// usage reads the points of a query, narrowed by path when it is not nil.
func (c *Client) usage(ctx context.Context, q UsageQuery, path *query) (map[string][]Point, error) {
	name, ok := intervals[q.Interval]
	if !ok && q.Interval != 0 {
		return nil, fmt.Errorf("the usage is read in buckets of 1m, 15m, 30m, 1h or 1d, not %s", q.Interval)
	}
	v := url.Values{}
	for _, m := range q.Metrics {
		v.Add("metrics[]", m)
	}
	if name != "" {
		v.Set("interval", name)
	}
	if q.ByResource {
		v.Add("dimensions[]", "resourceId")
	}
	for _, d := range q.Dimensions {
		v.Add("dimensions[]", d)
	}
	switch {
	case q.Top > 0:
		v.Set("orderBy", "value")
		v.Set("orderDir", "desc")
		v.Set("limit", strconv.Itoa(q.Top))
	default:
		v.Set("orderDir", "asc")
		v.Set("limit", "5000")
	}
	if !q.Start.IsZero() {
		v.Set("startAt", q.Start.UTC().Format(time.RFC3339))
	}
	if !q.End.IsZero() {
		v.Set("endAt", q.End.UTC().Format(time.RFC3339))
	}
	var filters []query
	if q.ResourceType != "" {
		filters = append(filters, equal("resourceType", q.ResourceType))
	}
	if q.ResourceID != "" {
		filters = append(filters, equal("resourceId", q.ResourceID))
	}
	if path != nil {
		filters = append(filters, *path)
	}
	for _, service := range q.Exclude {
		filters = append(filters, query{Method: "notEqual", Attribute: "service", Values: []any{service}})
	}
	for _, f := range filters {
		v.Add("queries[]", f.String())
	}
	var answer struct {
		Metrics []struct {
			Metric string                       `json:"metric"`
			Points []map[string]json.RawMessage `json:"points"`
		} `json:"metrics"`
	}
	if err := c.do(ctx, http.MethodGet, "/usage/"+string(q.Kind), v, nil, &answer); err != nil {
		return nil, err
	}
	out := make(map[string][]Point, len(q.Metrics))
	for _, m := range answer.Metrics {
		points := make([]Point, 0, len(m.Points))
		for _, p := range m.Points {
			var stamp string
			_ = json.Unmarshal(p["time"], &stamp)
			t, err := time.Parse(time.RFC3339Nano, stamp)
			if err != nil && name != "" {
				continue
			}
			value, _ := strconv.ParseFloat(string(p["value"]), 64)
			point := Point{Time: t, Value: value}
			_ = json.Unmarshal(p["resourceId"], &point.ResourceID)
			for _, d := range q.Dimensions {
				var dim string
				if json.Unmarshal(p[d], &dim) == nil && dim != "" {
					if point.Dims == nil {
						point.Dims = map[string]string{}
					}
					point.Dims[d] = dim
				}
			}
			points = append(points, point)
		}
		out[m.Metric] = points
	}
	return out, nil
}

// Bucketed lays points out in n buckets of interval from start, the value of each bucket the sum
// of the points in it. A gauge carries its last level into the buckets after it until it is
// sampled again, from a level sampled before start too, as it holds until it changes.
func Bucketed(points []Point, start time.Time, interval time.Duration, n int, gauge bool) []float64 {
	out := make([]float64, n)
	seen := make([]bool, n)
	carry, carried := 0.0, false
	for _, p := range points {
		i := int(p.Time.Sub(start) / interval)
		switch {
		case p.Time.Before(start):
			carry, carried = p.Value, true
		case i < n:
			if gauge {
				out[i] = p.Value
			} else {
				out[i] += p.Value
			}
			seen[i] = true
		}
	}
	if gauge {
		for i := range out {
			switch {
			case seen[i]:
				carry, carried = out[i], true
			case carried:
				out[i] = carry
			}
		}
	}
	return out
}
