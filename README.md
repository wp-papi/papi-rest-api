# Papi REST API

> Work in progress

[![Build Status](https://travis-ci.org/wp-papi/papi-rest-api.svg?branch=master)](https://travis-ci.org/wp-papi/papi-rest-api)

Add-on for the WordPress REST API, requires Papi 3.0.0.

## Addtional fields

### Fields

Post types that has page types will get addtional field attached to the object with the `fields` that contains all fields for the the current post page type. This can be removed.

Example response with additional `fields` field:

```json
[
  {
    "id": 1,
    "fields": {
      "name": "Fredrik"
    }
  }
]
```

### Page type field

```json
[
  {
    "id": 1,
    "page_type": "example-page-type"
  }
]
```

## Endpoints

### Fields

`GET /wp-json/papi/v1/fields/{id}`

Get all page types properties on a post.

`POST/PUT/PATCH /wp-json/papi/v1/fields/{id}`

Update multiple property values.

```json
{
  "properties": [
    {
      "slug":  "name",
      "value": "Fredrik"
    }
  ]
}
```

`GET /wp-json/papi/v1/fields/{id}/{slug}`

Get a single page type property on a post.

`POST/PUT/PATCH /wp-json/papi/v1/fields/{id}/{slug}`

Update a single page type property value on a post.

```json
{
  "value": "Fredrik"
}
```

`DELETE /wp-json/papi/v1/fields/{id}`

Delete multiple property values.

```json
{
  "properties": [
    {
      "slug":  "name"
    }
  ]
}
```

`DELETE /wp-json/papi/v1/fields/{id}/{slug}`

Delete a single page type property value on a post.

### Options

`GET /wp-json/papi/v1/options`

Get all option types properties.

`POST/PUT/PATCH /wp-json/papi/v1/options`

Update multiple property values.

```json
{
  "properties": [
    {
      "slug":  "name",
      "value": "Fredrik"
    }
  ]
}
```

`GET /wp-json/papi/v1/options/{slug}`

Get a single option type property.

`POST/PUT/PATCH /wp-json/papi/v1/options/{slug}`

Update a single option type property value.

```json
{
  "value": "Fredrik"
}
```

`DELETE /wp-json/papi/v1/options`

Delete multiple property values.

```json
{
  "properties": [
    {
      "slug":  "name"
    }
  ]
}
```

`DELETE /wp-json/papi/v1/options/{slug}`

Delete a single option type property value.

## Filters

`papi/rest/prepare_property_item` - Modify the property item that is returned to the REST API.

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
