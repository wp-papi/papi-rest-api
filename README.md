# Papi REST API

> Work in progress

[![Build Status](https://travis-ci.org/wp-papi/papi-rest-api.svg?branch=master)](https://travis-ci.org/wp-papi/papi-rest-api)

Add-on for the WordPress REST API, requires Papi 3.0.0.

## Endpoints

### Fields

`GET /wp-json/papi/v1/fields/{id}`

Get all page types properties on a post.

`POST/PUT/PATCH /wp-json/papi/v1/fields/{id}`

Update properties values.

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

`GET /wp-json/papi/v1/fields/{id}/{property_slug}`

Get a single page type property on a post.

`POST/PUT/PATCH /wp-json/papi/v1/fields/{id}/{property_slug}`

Update a single page type property value on a post.

```json
{
  "value": "Fredrik"
}
```

`DELETE /wp-json/papi/v1/fields/{id}/{property_slug}`

Delete a single page type property value on a post.

### Options

`GET /wp-json/papi/v1/options`

Get all option types properties.

`GET /wp-json/papi/v1/options/{property_slug}`

Get a single option type property.

`POST/PUT/PATCH /wp-json/papi/v1/options/{property_slug}`

Update a single option type property value.

```json
{
  "value": "Fredrik"
}
```

`DELETE /wp-json/papi/v1/options/{property_slug}`

Delete a single option type property value.

## Filters

`papi/rest/property_item` - Modify the property item that is returned to the REST API.

# License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
