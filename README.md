# Papi REST API

> Work in progress

[![Build Status](https://travis-ci.org/wp-papi/papi-rest-api.svg?branch=master)](https://travis-ci.org/wp-papi/papi-rest-api)

Add-on for the WordPress REST API, requires Papi 3.0.0.

## Endpoints

`GET /wp-json/papi/v1/options`

Get all option types properties.

`GET /wp-json/papi/v1/options/{option-name}`

Get a single option type property.

`PUT /wp-json/papi/v1/options/{option-name}`

Update a single option type property.

`DELETE /wp-json/papi/v1/options/{option-name}`

Delete a single option type property.

## Filters

`papi/rest/property_item` - Modify the property item that is returned to the REST API.

# License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
